<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum ParcelType: string
{
    case Envelope = 'Envelope';
    case Parcel = 'Parcel';
    case Pallet = 'Pallet';
}
