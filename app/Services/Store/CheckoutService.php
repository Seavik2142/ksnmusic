<?php

namespace App\Services\Store;

use App\Enums\Store\OrderStatus;
use App\Enums\Store\PaymentProvider;
use App\Enums\Store\PurchasableType;
use App\Models\Album;
use App\Models\Order;
use App\Models\Song;
use App\Models\User;
use App\Repositories\AlbumRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\SongRepository;
use App\Services\Payments\PaymentGatewayResolver;
use App\Services\SettingService;
use App\Values\Store\PaymentInitiation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly SongRepository $songRepository,
        private readonly AlbumRepository $albumRepository,
        private readonly OrganizationRepository $organizationRepository,
        private readonly EntitlementService $entitlementService,
        private readonly SettingService $settingService,
        private readonly PaymentGatewayResolver $gatewayResolver,
        private readonly OrderFulfillmentService $fulfillmentService,
    ) {}

    /**
     * @param array<int, array{type: string, id: string}> $requestedItems
     *
     * @return array{order: Order, payment: PaymentInitiation}
     */
    public function checkout(
        User $buyer,
        array $requestedItems,
        PaymentProvider $provider,
        ?string $paymentMethod = null,
    ): array {
        $gateway = $this->gatewayResolver->resolve($provider);

        throw_unless($gateway->isConfigured(), ValidationException::withMessages([
            'provider' => 'This payment method is not available.',
        ]));

        $order = $this->createOrder($buyer, $this->resolvePurchasables($buyer, $requestedItems), $provider);

        return ['order' => $order, 'payment' => $gateway->initiate($order, $paymentMethod)];
    }

    /**
     * Ask the order's payment provider whether it has been paid, and fulfil it if so.
     */
    public function refreshPaymentStatus(Order $order): Order
    {
        if (!$order->isPending()) {
            return $order;
        }

        $verification = $this->gatewayResolver->resolve($order->provider)->verify($order);

        return $this->fulfillmentService->applyVerification($order, $verification);
    }

    /**
     * @param array<int, array{type: string, id: string}> $requestedItems
     *
     * @return array<int, Song|Album>
     */
    private function resolvePurchasables(User $buyer, array $requestedItems): array
    {
        $purchasables = [];

        foreach ($requestedItems as $requestedItem) {
            $purchasable = $requestedItem['type'] === 'album'
                ? $this->albumRepository->findOne($requestedItem['id'])
                : $this->songRepository->findOne($requestedItem['id'], $buyer);

            throw_unless($purchasable?->isForSale(), ValidationException::withMessages([
                'items' => 'One of the items is not for sale.',
            ]));

            throw_if(
                $this->buyerAlreadyHasAccess($buyer, $purchasable),
                ValidationException::withMessages(['items' => 'You already have access to one of the items.']),
            );

            $purchasables[$purchasable::class . $purchasable->id] = $purchasable;
        }

        return array_values($purchasables);
    }

    private function buyerAlreadyHasAccess(User $buyer, Song|Album $purchasable): bool
    {
        return $purchasable instanceof Song
            ? $this->entitlementService->canListen($buyer, $purchasable)
            : $this->entitlementService->canSetPrice($buyer, $purchasable)
            || $this->entitlementService->hasPurchasedAlbum($buyer, $purchasable);
    }

    /** @param array<int, Song|Album> $purchasables */
    private function createOrder(User $buyer, array $purchasables, PaymentProvider $provider): Order
    {
        $storeSettings = $this->settingService->getStoreSettings(
            $this->organizationRepository->getRequestedOrganization(),
        );

        return DB::transaction(static function () use ($buyer, $purchasables, $provider, $storeSettings): Order {
            $order = Order::query()->create([
                'user_id' => $buyer->id,
                'status' => OrderStatus::PENDING,
                'provider' => $provider,
                'amount_cents' => array_sum(array_map(
                    static fn (Song|Album $purchasable): int => $purchasable->price_cents,
                    $purchasables,
                )),
                'currency' => config('koel.store.currency'),
            ]);

            foreach ($purchasables as $purchasable) {
                $sellerId = $purchasable->artist?->seller_id;

                $order
                    ->items()
                    ->create([
                        'purchasable_type' => $purchasable instanceof Song
                            ? PurchasableType::SONG->value
                            : PurchasableType::ALBUM->value,
                        'purchasable_id' => $purchasable->id,
                        'seller_id' => $sellerId,
                        'amount_cents' => $purchasable->price_cents,
                        ...$storeSettings->split($purchasable->price_cents, $sellerId !== null),
                    ]);
            }

            return $order->load('items');
        });
    }
}
