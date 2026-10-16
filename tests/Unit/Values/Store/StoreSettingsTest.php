<?php

namespace Tests\Unit\Values\Store;

use App\Values\Store\StoreSettings;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreSettingsTest extends TestCase
{
    #[Test]
    public function splitASaleBetweenThePlatformAndTheSeller(): void
    {
        self::assertSame(
            ['commission_cents' => 20, 'seller_earning_cents' => 79],
            StoreSettings::make(20)->split(99, hasSeller: true),
        );
    }

    #[Test]
    public function keepTheWholeSaleWhenTheArtistHasNoSeller(): void
    {
        self::assertSame(
            ['commission_cents' => 99, 'seller_earning_cents' => 0],
            StoreSettings::make(20)->split(99, hasSeller: false),
        );
    }

    #[Test]
    public function clampTheCommissionToAValidPercentage(): void
    {
        self::assertSame(0, StoreSettings::make(-5)->commissionPercent);
        self::assertSame(100, StoreSettings::make(150)->commissionPercent);
    }

    #[Test]
    public function fallBackToTheDefaultCommission(): void
    {
        self::assertSame(15, StoreSettings::fromArray([], 15)->commissionPercent);
        self::assertSame(30, StoreSettings::fromArray(['commission_percent' => 30], 15)->commissionPercent);
    }
}
