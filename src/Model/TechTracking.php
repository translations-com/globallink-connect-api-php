<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

use GlobalLink\RestClient\Exception\InvalidArgumentException;

/**
 * Technology tracking information for submissions.
 */
final readonly class TechTracking
{
    private const MAX_ADAPTOR_NAME = 50;
    private const MAX_ADAPTOR_VERSION = 20;
    private const MAX_CLIENT_VERSION = 20;
    private const MAX_TECHNOLOGY_PRODUCT = 20;

    public function __construct(
        public string $adaptorName,
        public string $adaptorVersion,
        public string $clientVersion,
        public string $technologyProduct,
    ) {
        if (strlen($adaptorName) > self::MAX_ADAPTOR_NAME) {
            throw new InvalidArgumentException(
                sprintf('adaptorName max length is %d', self::MAX_ADAPTOR_NAME)
            );
        }
        if (strlen($adaptorVersion) > self::MAX_ADAPTOR_VERSION) {
            throw new InvalidArgumentException(
                sprintf('adaptorVersion max length is %d', self::MAX_ADAPTOR_VERSION)
            );
        }
        if (strlen($clientVersion) > self::MAX_CLIENT_VERSION) {
            throw new InvalidArgumentException(
                sprintf('clientVersion max length is %d', self::MAX_CLIENT_VERSION)
            );
        }
        if (strlen($technologyProduct) > self::MAX_TECHNOLOGY_PRODUCT) {
            throw new InvalidArgumentException(
                sprintf('technologyProduct max length is %d', self::MAX_TECHNOLOGY_PRODUCT)
            );
        }
    }

    /**
     * Convert to array format for API requests.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'adaptorName' => $this->adaptorName,
            'adaptorVersion' => $this->adaptorVersion,
            'clientVersion' => $this->clientVersion,
            'technologyProduct' => $this->technologyProduct,
        ];
    }

    /**
     * Create a TechTracking from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            adaptorName: $data['adaptorName'] ?? '',
            adaptorVersion: $data['adaptorVersion'] ?? '',
            clientVersion: $data['clientVersion'] ?? '',
            technologyProduct: $data['technologyProduct'] ?? '',
        );
    }
}
