@extends('base')

@section('title', koel_branding('name'))

@push('scripts')
    @php
        $koelExtraGlobals = [
            'mailer_configured' => mailer_configured(),
            'sso_providers' => collect_sso_providers(),
            'sso_oidc_label' => config('services.oidc.button_label'),
            'accepted_audio_extensions' => collect_accepted_audio_extensions(),
        ];
    @endphp
    <script id="koel-extra-globals" type="application/json">{!! json_encode($koelExtraGlobals) !!}</script>
    <script>
        Object.assign(window.KOEL, JSON.parse(document.getElementById('koel-extra-globals').textContent));
    </script>

    @if (session()->has('demo_account'))
        <script id="koel-demo-account" type="application/json">{!! json_encode(session('demo_account')) !!}</script>
        <script>
            window.KOEL.demo_account = JSON.parse(document.getElementById('koel-demo-account').textContent);
        </script>
    @elseif (isset($token))
        <script id="koel-auth-token" type="application/json">{!! json_encode($token) !!}</script>
        <script>
            window.KOEL.auth_token = JSON.parse(document.getElementById('koel-auth-token').textContent);
        </script>
    @elseif (isset($twoFactorLoginToken))
        <script id="koel-2fa-token" type="application/json">{!! json_encode($twoFactorLoginToken) !!}</script>
        <script>
            window.KOEL.two_factor_login_token = JSON.parse(document.getElementById('koel-2fa-token').textContent);
        </script>
    @endif
    @vite(['resources/assets/js/app.ts'])
@endpush
