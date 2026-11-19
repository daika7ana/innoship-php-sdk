<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Labels;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\LabelFormat;
use Daika7ana\Innoship\Enums\LabelType;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Bulk label operation request.
 */
final readonly class BulkLabelRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<BulkLabelItem>|null $awBs
     */
    public function __construct(
        public ?LabelType $labelType = null,
        public ?LabelFormat $labelFormat = null,
        public bool $useFile = true,
        public bool $includeVoucher = false,
        public bool $skipCache = false,
        public bool $noReprint = false,
        public ?array $awBs = null,
    ) {}
}
