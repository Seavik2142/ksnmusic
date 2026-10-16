<?php

namespace Tests\Feature\Store;

use App\Enums\Store\OrderStatus;
use App\Enums\Store\PaymentVerificationStatus;
use App\Enums\Store\PurchasableType;
use App\Http\Integrations\PayPal\Requests\CaptureOrderRequest;
use App\Http\Integrations\PayPal\Requests\CreateOrderRequest;
use App\Http\Integrations\PayPal\Requests\GetAccessTokenRequest;
use App\Http\Integrations\PayPal\Requests\GetOrderRequest;
use App\Http\Integrations\PayWay\Requests\CheckTransactionRequest;
use App\Http\Resources\OrderResource;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Song;
use App\Models\User;
use App\Services\Store\EntitlementService;
use App\Services\Store\OrderFulfillmentService;
use App\Values\Store\PaymentVerification;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

use function Tests\create_user;

class CheckoutTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'services.paypal.client_id' => 'paypal-client',
            'services.paypal.client_secret' => 'paypal-secret',
            'services.payway.merchant_id' => 'ksn-merchant',
            'services.payway.api_key' => 'payway-key',
            'koel.store.commission_percent' => 20,
        ]);
    }

    #[Test]
    public function checkOutASongWithPayPal(): void
    {
        $this->fakePayPal();
        $seller = create_user();
        $song = Song::factory()->for(Artist::factory()->state(['seller_id' => $seller->id]))->createOne([
            'price_cents' => 99,
        ]);
        $buyer = create_user();

        $response = $this->postAs(
            'api/store/orders',
            [
                'items' => [['type' => 'song', 'id' => $song->id]],
                'provider' => 'paypal',
            ],
            $buyer,
        );

        $response
            ->assertCreated()
            ->assertJsonStructure(['order' => OrderResource::JSON_STRUCTURE, 'payment' => ['url', 'method', 'fields']])
            ->assertJsonPath('payment.url', 'https://paypal.test/approve')
            ->assertJsonPath('payment.method', 'GET')
            ->assertJsonPath('order.amount_cents', 99)
            ->assertJsonPath('order.status', 'pending');

        /** @var Order $order */
        $order = Order::query()->sole();
        self::assertSame('PAYPAL-ORDER-1', $order->provider_reference);
        self::assertSame(20, $order->items->sole()->commission_cents);
        self::assertSame(79, $order->items->sole()->seller_earning_cents);
        self::assertSame($seller->id, $order->items->sole()->seller_id);
    }

    #[Test]
    public function thePlatformKeepsTheSaleWhenTheArtistHasNoSeller(): void
    {
        $this->fakePayPal();
        $song = Song::factory()->createOne(['price_cents' => 150]);

        $this->postAs(
            'api/store/orders',
            [
                'items' => [['type' => 'song', 'id' => $song->id]],
                'provider' => 'paypal',
            ],
            create_user(),
        )->assertCreated();

        $item = Order::query()->sole()->items->sole();
        self::assertNull($item->seller_id);
        self::assertSame(150, $item->commission_cents);
        self::assertSame(0, $item->seller_earning_cents);
    }

    #[Test]
    public function checkOutAnAlbumWithPayWayKhqr(): void
    {
        $album = Album::factory()->createOne(['price_cents' => 499]);

        $response = $this->postAs(
            'api/store/orders',
            [
                'items' => [['type' => 'album', 'id' => $album->id]],
                'provider' => 'payway',
                'payment_method' => 'khqr',
            ],
            create_user(),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('payment.method', 'POST')
            ->assertJsonPath(
                'payment.url',
                'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase',
            )
            ->assertJsonPath('payment.fields.merchant_id', 'ksn-merchant')
            ->assertJsonPath('payment.fields.amount', '4.99')
            ->assertJsonPath('payment.fields.currency', 'USD')
            ->assertJsonPath('payment.fields.payment_option', 'abapay_khqr')
            ->assertJsonPath('payment.fields.return_url', base64_encode(route('store.payway.callback')));

        $order = Order::query()->sole();
        self::assertSame(20, strlen($order->provider_reference));
        self::assertSame($order->provider_reference, $response->json('payment.fields.tran_id'));
        self::assertNotEmpty($response->json('payment.fields.hash'));
    }

    #[Test]
    public function refuseToSellAFreeSong(): void
    {
        $song = Song::factory()->createOne(['price_cents' => null]);

        $this->postAs(
            'api/store/orders',
            [
                'items' => [['type' => 'song', 'id' => $song->id]],
                'provider' => 'paypal',
            ],
            create_user(),
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertSame(0, Order::query()->count());
    }

    #[Test]
    public function refuseToSellASongTheBuyerAlreadyOwns(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);
        $buyer = create_user();
        $this->givePurchase($buyer, PurchasableType::SONG, $song->id);

        $this->postAs(
            'api/store/orders',
            [
                'items' => [['type' => 'song', 'id' => $song->id]],
                'provider' => 'paypal',
            ],
            $buyer,
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Test]
    public function refuseToSellASellerTheirOwnSong(): void
    {
        $seller = create_user();
        $song = Song::factory()->for(Artist::factory()->state(['seller_id' => $seller->id]))->createOne([
            'price_cents' => 99,
        ]);

        $this->postAs(
            'api/store/orders',
            [
                'items' => [['type' => 'song', 'id' => $song->id]],
                'provider' => 'paypal',
            ],
            $seller,
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Test]
    public function refuseAPaymentProviderThatIsNotConfigured(): void
    {
        config(['services.paypal.client_id' => null]);
        $song = Song::factory()->createOne(['price_cents' => 99]);

        $this->postAs(
            'api/store/orders',
            [
                'items' => [['type' => 'song', 'id' => $song->id]],
                'provider' => 'paypal',
            ],
            create_user(),
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Test]
    public function capturingAnApprovedPayPalPaymentUnlocksTheSong(): void
    {
        $this->fakePayPal(captureAmount: '0.99');
        [$buyer, $order, $song] = $this->createPendingPayPalOrder();

        $this->getAs("api/store/orders/{$order->id}", $buyer)->assertOk()->assertJsonPath('status', 'paid');

        self::assertTrue($buyer->can('listen', $song));
    }

    #[Test]
    public function returningFromPayPalCapturesAndSendsTheBuyerToTheOrderPage(): void
    {
        $this->fakePayPal(captureAmount: '0.99');
        [, $order] = $this->createPendingPayPalOrder();

        $this->get("store/paypal/return/{$order->id}")->assertRedirect(client_url("store/orders/{$order->id}"));

        self::assertSame(OrderStatus::PAID, $order->refresh()->status);
    }

    #[Test]
    public function aPaymentForTheWrongAmountDoesNotUnlockAnything(): void
    {
        $this->fakePayPal(captureAmount: '0.01');
        [$buyer, $order, $song] = $this->createPendingPayPalOrder();

        $this->getAs("api/store/orders/{$order->id}", $buyer)->assertJsonPath('status', 'pending');

        self::assertFalse($buyer->can('listen', $song));
    }

    #[Test]
    public function aPayWayCallbackIsConfirmedWithPayWayBeforeFulfilling(): void
    {
        Saloon::fake([
            CheckTransactionRequest::class => MockResponse::make(body: [
                'data' => [
                    'payment_status_code' => 0,
                    'payment_status' => 'APPROVED',
                    'payment_amount' => 4.99,
                    'payment_currency' => 'USD',
                ],
                'status' => ['code' => '00'],
            ]),
        ]);

        $buyer = create_user();
        $album = Album::factory()->createOne(['price_cents' => 499]);
        $order = $this->createPendingOrder(
            $buyer,
            'payway',
            'TRANSACTION-ID-12345',
            PurchasableType::ALBUM,
            $album->id,
            499,
        );

        $this->postJson('api/store/payway/callback', ['tran_id' => 'TRANSACTION-ID-12345', 'status' => 0])->assertOk();

        self::assertSame(OrderStatus::PAID, $order->refresh()->status);
        self::assertTrue(app(EntitlementService::class)->hasPurchasedAlbum($buyer, $album));
    }

    #[Test]
    public function aDeclinedPayWayPaymentFailsTheOrder(): void
    {
        Saloon::fake([
            CheckTransactionRequest::class => MockResponse::make(body: [
                'data' => ['payment_status_code' => 3, 'payment_status' => 'DECLINED'],
                'status' => ['code' => '00'],
            ]),
        ]);

        $buyer = create_user();
        $song = Song::factory()->createOne(['price_cents' => 99]);
        $order = $this->createPendingOrder(
            $buyer,
            'payway',
            'TRANSACTION-ID-99999',
            PurchasableType::SONG,
            $song->id,
            99,
        );

        $this->postJson('api/store/payway/callback', ['tran_id' => 'TRANSACTION-ID-99999'])->assertOk();

        self::assertSame(OrderStatus::FAILED, $order->refresh()->status);
    }

    #[Test]
    public function aCallbackForAnUnknownTransactionIsIgnored(): void
    {
        Saloon::fake([]);

        $this->postJson('api/store/payway/callback', ['tran_id' => 'NOPE'])->assertNotFound();
    }

    #[Test]
    public function fulfillingTheSameOrderTwiceGrantsOnePurchase(): void
    {
        $buyer = create_user();
        $song = Song::factory()->createOne(['price_cents' => 99]);
        $order = $this->createPendingOrder($buyer, 'paypal', 'P-1', PurchasableType::SONG, $song->id, 99);
        $paid = PaymentVerification::make(PaymentVerificationStatus::PAID, 99, 'USD');

        app(OrderFulfillmentService::class)->applyVerification($order, $paid);
        app(OrderFulfillmentService::class)->applyVerification($order, $paid);

        self::assertSame(1, Purchase::query()->whereBelongsTo($buyer)->count());
    }

    #[Test]
    public function aBuyerCannotSeeSomeoneElsesOrder(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);
        $order = $this->createPendingOrder(create_user(), 'paypal', 'P-2', PurchasableType::SONG, $song->id, 99);

        $this->getAs("api/store/orders/{$order->id}", create_user())->assertForbidden();
    }

    private function fakePayPal(string $captureAmount = '0.99'): void
    {
        Saloon::fake([
            GetAccessTokenRequest::class => MockResponse::make(body: ['access_token' => 'token', 'expires_in' => 3600]),
            CreateOrderRequest::class => MockResponse::make(body: [
                'id' => 'PAYPAL-ORDER-1',
                'status' => 'PAYER_ACTION_REQUIRED',
                'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/approve']],
            ]),
            GetOrderRequest::class => MockResponse::make(body: ['status' => 'APPROVED']),
            CaptureOrderRequest::class => MockResponse::make(body: [
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'payments' => [
                        'captures' => [[
                            'status' => 'COMPLETED',
                            'amount' => ['value' => $captureAmount, 'currency_code' => 'USD'],
                        ]],
                    ],
                ]],
            ]),
        ]);
    }

    /** @return array{0: User, 1: Order, 2: Song} */
    private function createPendingPayPalOrder(): array
    {
        $buyer = create_user();
        $song = Song::factory()->createOne(['price_cents' => 99]);
        $order = $this->createPendingOrder($buyer, 'paypal', 'PAYPAL-ORDER-1', PurchasableType::SONG, $song->id, 99);

        return [$buyer, $order, $song];
    }

    private function createPendingOrder(
        User $buyer,
        string $provider,
        string $providerReference,
        PurchasableType $type,
        string $purchasableId,
        int $amountCents,
    ): Order {
        $order = Order::factory()->for($buyer)->createOne([
            'provider' => $provider,
            'provider_reference' => $providerReference,
            'amount_cents' => $amountCents,
        ]);

        $order
            ->items()
            ->create([
                'purchasable_type' => $type->value,
                'purchasable_id' => $purchasableId,
                'amount_cents' => $amountCents,
                'commission_cents' => $amountCents,
                'seller_earning_cents' => 0,
            ]);

        return $order;
    }

    private function givePurchase(User $buyer, PurchasableType $type, string $purchasableId): void
    {
        Purchase::factory()->for($buyer)->createOne([
            'purchasable_type' => $type->value,
            'purchasable_id' => $purchasableId,
        ]);
    }
}
