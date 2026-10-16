<?php

namespace App\Http\Requests\API\Store;

use App\Http\Requests\Request;

/**
 * @property ?int $price_cents
 */
class UpdatePriceRequest extends Request
{
    public const int MAX_PRICE_CENTS = 100_000;

    /** @inheritdoc */
    public function rules(): array
    {
        return [
            'price_cents' => ['present', 'nullable', 'integer', 'min:0', 'max:' . self::MAX_PRICE_CENTS],
        ];
    }

    /** @inheritdoc */
    public function messages(): array
    {
        return [
            'price_cents.max' => 'The price cannot be more than $1,000.',
        ];
    }
}
