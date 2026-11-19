<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum CallCourierPickupMode: string
{
    case FixedTime = 'FixedTime';
    case FirstAvailable = 'FirstAvailable';
    case EndOfDay = 'EndOfDay';
}
