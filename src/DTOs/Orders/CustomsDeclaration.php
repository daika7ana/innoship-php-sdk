<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\PurposeOfShipment;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class CustomsDeclaration implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<CustomsDeclarationIdentifier>|null $exporterIdentifierTypes
     * @param list<CustomsDeclarationIdentifier>|null $importerIdentifierTypes
     * @param list<CustomsDeclarationAttachment>|null $attachments
     */
    public function __construct(
        public ?OrderRequestAddress $exporter = null,
        public ?OrderRequestAddress $importer = null,
        public ?string $exporterTaxId = null,
        public ?string $exporterEORI = null,
        public ?string $exporterUkimsNumber = null,
        public ?string $exporterBN = null,
        public ?array $exporterIdentifierTypes = null,
        public ?string $importerTaxId = null,
        public ?string $importerEORI = null,
        public ?string $importerUkimsNumber = null,
        public ?array $importerIdentifierTypes = null,
        public ?PurposeOfShipment $reason = null,
        public ?string $incoterm = null,
        public ?array $attachments = null,
    ) {}
}
