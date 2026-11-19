<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Incoterm;
use Daika7ana\Innoship\Enums\MarketplaceIntegration;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderExtra implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<string>|null $notificationAttachments
     */
    public function __construct(
        public ?float $bankRepaymentAmount = null,
        public ?string $bankRepaymentCurrency = null,
        public ?float $cashOnDeliveryAmount = null,
        public ?string $cashOnDeliveryAmountCurrency = null,
        public ?bool $cashOnDeliveryPayoutToLocation = null,
        public ?float $declaredValueAmount = null,
        public ?string $declaredValueAmountCurrency = null,
        public ?bool $openPackage = null,
        public ?bool $saturdayDelivery = null,
        public ?float $insuranceAmount = null,
        public ?string $insuranceAmountCurrency = null,
        public ?string $reference1 = null,
        public ?string $reference2 = null,
        public ?string $reference3 = null,
        public ?string $reference4 = null,
        public ?bool $returnOfDocuments = null,
        public ?string $returnOfDocumentsComment = null,
        public ?bool $returnPackage = null,
        public ?bool $returnVoucher = null,
        public ?string $bank = null,
        public ?string $bankIBAN = null,
        public ?string $bankAccountHolder = null,
        public ?CustomsDeclaration $customsDeclaration = null,
        public ?MarketplaceIntegration $marketPlace = null,
        public ?string $uitCode = null,
        public ?Incoterm $incoterm = null,
        public ?string $plannedDeliveryBeginDate = null,
        public ?string $plannedDeliveryEndDate = null,
        public ?array $notificationAttachments = null,
    ) {}
}
