<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum CustomsDeclarationIdentifierType: string
{
    case TaxID = 'TaxID';
    case BN = 'BN';
    case EORI = 'EORI';
    case UKIMS = 'UKIMS';
    case ZAZ = 'ZAZ';
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';
    case EIN = 'EIN';
    case TIN = 'TIN';
}
