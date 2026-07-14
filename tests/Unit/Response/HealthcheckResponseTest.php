<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Unit\Response;

use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\Response\HealthcheckResponse;

/**
 * Unit tests for HealthcheckResponse.
 */
class HealthcheckResponseTest extends TestCase
{
    public function testFromResponseHealthy(): void
    {
        $response = HealthcheckResponse::fromResponse('OK', 200);

        $this->assertTrue($response->healthy);
        $this->assertSame('OK', $response->message);
    }

    public function testFromResponseUnhealthy(): void
    {
        $response = HealthcheckResponse::fromResponse('Service Unavailable', 503);

        $this->assertFalse($response->healthy);
        $this->assertSame('Service Unavailable', $response->message);
    }

    public function testFromResponseWithWhitespace(): void
    {
        $response = HealthcheckResponse::fromResponse("  OK  \n", 200);

        $this->assertTrue($response->healthy);
        $this->assertSame('OK', $response->message);
    }

    public function testFromResponseEmptyBody(): void
    {
        $response = HealthcheckResponse::fromResponse('', 200);

        $this->assertTrue($response->healthy);
        $this->assertSame('', $response->message);
    }

    public function testFromResponse404(): void
    {
        $response = HealthcheckResponse::fromResponse('Not Found', 404);

        $this->assertFalse($response->healthy);
        $this->assertSame('Not Found', $response->message);
    }

    public function testFromResponse500(): void
    {
        $response = HealthcheckResponse::fromResponse('Internal Server Error', 500);

        $this->assertFalse($response->healthy);
        $this->assertSame('Internal Server Error', $response->message);
    }

    public function testConstructor(): void
    {
        $response = new HealthcheckResponse(
            healthy: true,
            message: 'All systems operational'
        );

        $this->assertTrue($response->healthy);
        $this->assertSame('All systems operational', $response->message);
    }
}
