<?php

namespace App\Http\Requests\API\Store;

use App\Enums\Store\PaymentProvider;
use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

/**
 * @property-read array<int, array{type: string, id: string}> $items
 * @property-read string $provider
 * @property-read ?string $payment_method
 */
class CheckoutRequest extends Request
{
    /** @inheritdoc */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.type' => ['required', Rule::in(['song', 'album'])],
            'items.*.id' => ['required', 'string', 'max:36'],
            'provider' => ['required', Rule::enum(PaymentProvider::class)],
            'payment_method' => ['nullable', Rule::in(['khqr', 'card'])],
        ];
    }
}
