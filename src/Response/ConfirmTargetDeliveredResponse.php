<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from confirming target delivery.
 */
readonly class ConfirmTargetDeliveredResponse
{
    /**
     * @param list<int> $successfullyDeliveredTargets
     * @param list<int> $failedToDeliverTargets
     */
    public function __construct(
        public array $successfullyDeliveredTargets = [],
        public array $failedToDeliverTargets = [],
    ) {
    }

    /**
     * Create a ConfirmTargetDeliveredResponse from a JSON string.
     */
    public static function fromJson(string $json): self
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        return self::fromArray($data);
    }

    /**
     * Create a ConfirmTargetDeliveredResponse from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            successfullyDeliveredTargets: $data['successfullyDeliveredTargets'] ?? [],
            failedToDeliverTargets: $data['failedToDeliverTargets'] ?? [],
        );
    }
}
