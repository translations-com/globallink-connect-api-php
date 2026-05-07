<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Http;

use TransPerfect\GlobalLink\Exception\GlobalLinkException;

/**
 * HTTP client implementation using PHP's native stream functions.
 *
 * This is the default transport that uses fopen() with stream_context_create()
 * for making HTTP requests without external dependencies.
 */
class StreamHttpClient implements HttpClientInterface
{
    /**
     * @param bool $verifyPeer Whether to verify SSL peer certificate
     * @param bool $verifyPeerName Whether to verify SSL peer name
     * @param int $timeout Connection timeout in seconds
     */
    public function __construct(
        private readonly bool $verifyPeer = true,
        private readonly bool $verifyPeerName = true,
        private readonly int $timeout = 30,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $method, string $url, array $headers, ?string $body = null): HttpResponse
    {
        $contextOptions = [
            'http' => [
                'method' => strtoupper($method),
                'ignore_errors' => true,
                'timeout' => $this->timeout,
                'header' => $this->formatHeaders($headers),
            ],
            'ssl' => [
                'verify_peer' => $this->verifyPeer,
                'verify_peer_name' => $this->verifyPeerName,
            ],
        ];

        if ($body !== null) {
            $contextOptions['http']['content'] = $body;
        }

        $context = stream_context_create($contextOptions);

        $fp = @fopen($url, 'rb', false, $context);
        if ($fp === false) {
            $error = error_get_last();
            throw new GlobalLinkException(
                sprintf('Unable to connect to %s: %s', $url, $error['message'] ?? 'Unknown error')
            );
        }

        $responseBody = stream_get_contents($fp);
        if ($responseBody === false) {
            fclose($fp);
            throw new GlobalLinkException('Failed to read response body');
        }

        $meta = stream_get_meta_data($fp);
        fclose($fp);

        $statusCode = 0;
        $responseHeaders = [];

        if (isset($meta['wrapper_data']) && is_array($meta['wrapper_data'])) {
            foreach ($meta['wrapper_data'] as $header) {
                if (preg_match('#^HTTP/\d+(?:\.\d+)? (\d+)#', $header, $matches)) {
                    $statusCode = (int) $matches[1];
                } elseif (str_contains($header, ':')) {
                    [$name, $value] = explode(':', $header, 2);
                    $normalizedName = trim($name);
                    $responseHeaders[$normalizedName][] = trim($value);
                }
            }
        }

        return new HttpResponse($statusCode, $responseBody, $responseHeaders);
    }

    /**
     * Format headers array into the format expected by stream_context_create.
     *
     * @param array<string, string> $headers
     */
    private function formatHeaders(array $headers): string
    {
        $formatted = [];
        foreach ($headers as $name => $value) {
            $formatted[] = sprintf('%s: %s', $name, $value);
        }
        return implode("\r\n", $formatted);
    }
}
