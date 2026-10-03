<?php

namespace App\Http\Requests\API\Album;

use App\Http\Requests\API\Request;
use App\Rules\ValidImageData;

/**
 * @property-read string $name
 * @property-read ?string $artist_name
 * @property-read ?int $year
 * @property-read ?string $cover
 */
class AlbumStoreRequest extends Request
{
    /** @inheritDoc */
    public function rules(): array
    {
        return [
            'name' => ['string', 'required', 'max:255'],
            'artist_name' => ['string', 'nullable', 'max:255'],
            'year' => ['integer', 'nullable'],
            'cover' => ['string', 'sometimes', 'nullable', new ValidImageData()],
        ];
    }
}
