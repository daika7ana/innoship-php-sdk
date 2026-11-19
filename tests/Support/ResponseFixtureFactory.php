<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Support;

use GuzzleHttp\Psr7\Response;

final class ResponseFixtureFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderCreated(): array
    {
        return [
            'clientOrderId' => 662,
            'courierShipmentId' => '877551879',
            'courierShipmentVoucher' => null,
            'externalOrderId' => null,
            'courier' => 1,
            'courierServiceName' => 'Standard',
            'courierParcelsBarcodes' => [
                ['sequenceNo' => 1, 'barcode' => 'BC123', 'customerBarcode' => 'CUST1', 'additionalBarcodes' => []],
            ],
            'price' => [
                'amount' => 13.36,
                'vat' => 2.54,
                'totalAmount' => 15.9,
                'currency' => 'RON',
                'priceComponents' => ['FuelSurcharge' => 1.2],
            ],
            'calculatedDeliveryDate' => '2019-10-03T00:00:00Z',
            'trackPageUrl' => 'https://track/877551879',
            'trackingNumber' => '877551879',
            'courierTrackPageUrl' => null,
            'returnToLockerCode' => null,
            'courierResponse' => null,
            'extra' => null,
            'address' => null,
            'receiverFixedLocationOverwrite' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationError(string $message = 'Invalid order data'): array
    {
        return [
            'errors' => [
                [
                    'message' => $message,
                    'details' => ['addressTo is required'],
                    'categoryCode' => 'Validation',
                    'subcategoryCode' => 'Order',
                ],
            ],
            'correlationId' => 'corr-123',
        ];
    }
}
