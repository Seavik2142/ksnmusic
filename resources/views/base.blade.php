<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>

    <meta name="description" content="{{ config('app.tagline') }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">

    <meta name="theme-color" content="#111111">
    <meta name="msapplication-navbutton-color" content="#111111">

    <link rel="stylesheet" href="{{ static_url('css/layer-order.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;family=Kantumruy+Pro:wght@400;600;700&amp;family=Moul&amp;family=Outfit:wght@300;400;500;600;700;800&amp;family=Siemreap&amp;display=swap" rel="stylesheet">

    <link rel="manifest" crossorigin="use-credentials" href="@yield('manifest', route('manifest'))" />
    <meta name="msapplication-config" content="{{ static_url('browserconfig.xml') }}" />
    @if (koel_branding('logo'))
        <link rel="icon" href="{{ koel_branding('logo') }}">
        <link rel="apple-touch-icon" href="{{ koel_branding('logo') }}">
    @else
        <link rel="icon" href="{{ static_url('img/favicon.ico') }}" sizes="32x32">
        <link rel="icon" type="image/svg+xml" href="{{ static_url('img/favicon.svg') }}">
        <link rel="apple-touch-icon" href="{{ static_url('img/icon.png') }}">
    @endif

    @unless(\App\Facades\License::isPlus())
        <script src="https://app.lemonsqueezy.com/js/lemon.js" defer></script>
    @endunless

    <script>
        // Work around for "global is not defined" error with local-storage.js
        window.global = window
    </script>
</head>
<body class="text-k-fg-70">
<div id="app"></div>

@php
    $koelGlobals = [
        'base_url' => base_url(),
        'build' => koel_build_id(),
        'clean_urls' => (bool) config('koel.clean_urls.enabled'),
        'is_demo' => config('koel.misc.demo'),
        'pusher' => [
            'app_key' => config('broadcasting.connections.pusher.key'),
            'app_cluster' => config('broadcasting.connections.pusher.options.cluster'),
        ],
        'branding' => koel_branding(),
        'gravatar' => [
            'url' => config('services.gravatar.url'),
            'default' => config('services.gravatar.default'),
        ],
    ];
@endphp
<script id="koel-globals" type="application/json">{!! json_encode($koelGlobals) !!}</script>
<script>
    window.KOEL = JSON.parse(document.getElementById('koel-globals').textContent);
</script>

@stack('scripts')
</body>
</html>
