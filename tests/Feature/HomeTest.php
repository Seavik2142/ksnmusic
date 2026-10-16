<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeTest extends TestCase
{
    #[Test]
    public function renders(): void
    {
        $this
            ->withoutVite()
            ->get('/')
            ->assertOk()
            ->assertSee('window.KOEL = ', false)
            ->assertSee('accepted_audio_extensions', false)
            ->assertSee('mailer_configured', false)
            ->assertSee('sso_providers', false)
            ->assertSee('branding', false);
    }

    #[Test]
    public function usesTheBundledBrandIconsWithoutACustomLogo(): void
    {
        $this
            ->withoutVite()
            ->get('/')
            ->assertOk()
            ->assertSee('<link rel="icon" href="' . static_url('img/favicon.ico') . '" sizes="32x32">', false)
            ->assertSee('<link rel="icon" type="image/svg+xml" href="' . static_url('img/favicon.svg') . '">', false)
            ->assertSee('<link rel="apple-touch-icon" href="' . static_url('img/icon.png') . '">', false);
    }
}
