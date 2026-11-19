<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Enums;

enum UploadDocumentType: string
{
    case AuthorizationForm = 'AuthorizationForm';
    case CommercialInvoice = 'CommercialInvoice';
    case CertificateOfOrigin = 'CertificateOfOrigin';
    case ExportAccompanyingDocument = 'ExportAccompanyingDocument';
    case ExportLicense = 'ExportLicense';
    case ImportPermit = 'ImportPermit';
    case OneTimeNAFTA = 'OneTimeNAFTA';
    case OtherDocument = 'OtherDocument';
    case PowerOfAttorney = 'PowerOfAttorney';
    case PackingList = 'PackingList';
    case SEDDocument = 'SEDDocument';
    case ShippersLetterOfInstruction = 'ShippersLetterOfInstruction';
    case Declaration = 'Declaration';
}
