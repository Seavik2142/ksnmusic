<?php

namespace App\Values\Store;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

final readonly class StoreSettings implements Arrayable
{
    private function __construct(
        public int $commissionPercent,
    ) {}

    public static function make(int $commissionPercent): self
    {
        return new self(commissionPercent: max(0, min(100, $commissionPercent)));
    }

    /** @param array<string, mixed> $settings */
    public static function fromArray(array $settings, int $defaultCommissionPercent): self
    {
        return self::make((int) Arr::get($settings, 'commission_percent', $defaultCommissionPercent));
    }

    /** @return array{commission_cents: int, seller_earning_cents: int} */
    public function split(int $amountCents, bool $hasSeller): array
    {
        if (!$hasSeller) {
            return ['commission_cents' => $amountCents, 'seller_earning_cents' => 0];
        }

        $commissionCents = (int) round(($amountCents * $this->commissionPercent) / 100);

        return ['commission_cents' => $commissionCents, 'seller_earning_cents' => $amountCents - $commissionCents];
    }

    /** @inheritdoc */
    public function toArray(): array
    {
        return ['commission_percent' => $this->commissionPercent];
    }
}
