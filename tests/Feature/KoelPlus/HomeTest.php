<?php

namespace Tests\Feature\KoelPlus;

use App\Models\Organization;
use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\PlusTestCase;

class HomeTest extends PlusTestCase
{
    #[Test]
    public function usesTheCustomLogoAsTheBrowserIcon(): void
    {
        Setting::set(
            'branding',
            [
                'name' => 'Little Bird',
                'logo' => 'https://example.com/custom-logo.png',
                'cover' => null,
            ],
            Organization::default(),
        );

        $this
            ->withoutVite()
            ->get('/')
            ->assertOk()
            ->assertSee('<link rel="icon" href="https://example.com/custom-logo.png">', false)
            ->assertSee('<link rel="apple-touch-icon" href="https://example.com/custom-logo.png">', false)
            ->assertDontSee('img/favicon.svg', false);
    }
}
