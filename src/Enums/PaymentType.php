<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum PaymentType: string
{
    case Sender = 'Sender';
    case Recipient = 'Recipient';
    case ThirdParty = 'ThirdParty';
}
