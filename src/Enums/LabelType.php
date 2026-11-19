<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum LabelType: string
{
    case Pdf = 'PDF';
    case Html = 'Html';
    case Zpl = 'Zpl';
    case Epl = 'Epl';
    case Clp = 'Clp';
}
