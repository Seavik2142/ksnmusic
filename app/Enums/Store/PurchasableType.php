<?php

namespace App\Enums\Store;

enum PurchasableType: string
{
    case SONG = 'playable';
    case ALBUM = 'album';
}
