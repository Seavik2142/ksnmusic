<?php

namespace App\Values\Store;

use Illuminate\Contracts\Support\Arrayable;

/**
 * How the browser continues to the payment provider: either a plain redirect,
 * or a form POST (with signed fields) to the provider's hosted checkout.
 */
final readonly class PaymentInitiation implements Arrayable
{
    /** @param array<string, string> $fields */
    private function __construct(
        public string $url,
        public string $method,
        public array $fields,
    ) {}

    public static function redirect(string $url): self
    {
        return new self(url: $url, method: 'GET', fields: []);
    }

    /** @param array<string, string> $fields */
    public static function formPost(string $url, array $fields): self
    {
        return new self(url: $url, method: 'POST', fields: $fields);
    }

    /** @inheritdoc */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'method' => $this->method,
            'fields' => (object) $this->fields,
        ];
    }
}
