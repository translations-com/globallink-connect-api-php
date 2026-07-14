<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\Exception\ApiException;
use GlobalLink\RestClient\Exception\AuthenticationException;
use GlobalLink\RestClient\Exception\GlobalLinkException;
use GlobalLink\RestClient\Exception\InvalidArgumentException;
use GlobalLink\RestClient\Exception\RateLimitException;

/**
 * Tests for exception hierarchy.
 */
class ExceptionHierarchyTest extends TestCase
{
    public function testGlobalLinkExceptionExtendsRuntimeException(): void
    {
        $exception = new GlobalLinkException('Test message');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }

    public function testApiExceptionExtendsGlobalLinkException(): void
    {
        $exception = new ApiException('API error', 500, 'response body');

        $this->assertInstanceOf(GlobalLinkException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame(500, $exception->getStatusCode());
        $this->assertSame('response body', $exception->getResponseBody());
    }

    public function testAuthenticationExceptionExtendsApiException(): void
    {
        $exception = new AuthenticationException('Auth failed', 401);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertInstanceOf(GlobalLinkException::class, $exception);
        $this->assertSame(401, $exception->getStatusCode());
    }

    public function testRateLimitExceptionExtendsApiException(): void
    {
        $exception = new RateLimitException('Rate limited', 60, 100, 0, 1704110400);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertInstanceOf(GlobalLinkException::class, $exception);
        $this->assertSame(429, $exception->getStatusCode());
        $this->assertSame(60, $exception->getRetryAfter());
        $this->assertSame(100, $exception->getRateLimitLimit());
        $this->assertSame(0, $exception->getRateLimitRemaining());
        $this->assertSame(1704110400, $exception->getRateLimitReset());
    }

    public function testInvalidArgumentExceptionExtendsBuiltIn(): void
    {
        $exception = new InvalidArgumentException('Invalid input');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertSame('Invalid input', $exception->getMessage());
    }

    public function testAuthenticationExceptionCanBeCaughtAsApiException(): void
    {
        $caught = false;

        try {
            throw new AuthenticationException('Auth failed');
        } catch (ApiException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testRateLimitExceptionCanBeCaughtAsApiException(): void
    {
        $caught = false;

        try {
            throw new RateLimitException('Rate limited');
        } catch (ApiException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testAllSdkExceptionsCanBeCaughtAsGlobalLinkException(): void
    {
        $exceptions = [
            new GlobalLinkException('Base'),
            new ApiException('API', 500),
            new AuthenticationException('Auth'),
            new RateLimitException('Rate'),
        ];

        foreach ($exceptions as $exception) {
            try {
                throw $exception;
            } catch (GlobalLinkException $e) {
                $this->assertSame($exception->getMessage(), $e->getMessage());
            }
        }
    }
}
