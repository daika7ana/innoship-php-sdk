<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum DocumentType: string
{
    case ConsignmentNote = 'ConsignmentNote';
    case ExpressWaybill = 'ExpressWaybill';
    case CustomsDeclaration = 'CustomsDeclaration';
    case Invoice = 'Invoice';
    case WorldEase = 'WorldEase';
    case ProofOfDelivery = 'ProofOfDelivery';
}
