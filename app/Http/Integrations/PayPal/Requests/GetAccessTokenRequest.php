<?php

namespace App\Http\Integrations\PayPal\Requests;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;
use SensitiveParameter;

class GetAccessTokenRequest extends Request implements HasBody
{
    use HasFormBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $clientId,
        #[SensitiveParameter]
        private readonly string $clientSecret,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/oauth2/token';
    }

    protected function defaultAuth(): Authenticator
    {
        return new BasicAuthenticator($this->clientId, $this->clientSecret);
    }

    /** @inheritdoc */
    protected function defaultBody(): array
    {
        return ['grant_type' => 'client_credentials'];
    }
}
