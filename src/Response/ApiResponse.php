<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Base class for all API responses.
 *
 * Carries HTTP metadata so callers can inspect status, errors, headers, and
 * the raw response body without exceptions for transport failures or non-success
 * HTTP statuses.
 *
 * Transport/network failures use responseCode 0 (no HTTP response received).
 * Only a usable 2xx response (with errorMessage === null) is considered success.
 */
abstract readonly class ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public int $responseCode = 200,
        public ?string $errorMessage = null,
        public array $headers = [],
        public string $responseBody = '',
    ) {
    }

    /**
     * Whether the HTTP status is a successful 2xx code.
     */
    public static function isHttpSuccessStatus(int $statusCode): bool
    {
        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Whether this is a usable success: 2xx status and no errorMessage.
     */
    public function isSuccess(): bool
    {
        return self::isHttpSuccessStatus($this->responseCode) && $this->errorMessage === null;
    }

    /**
     * Whether this response should be treated as a failure.
     *
     * True for transport errors (0), 1xx/3xx/4xx/5xx, and 2xx responses whose
     * body could not be used (errorMessage set).
     */
    public function isError(): bool
    {
        return !$this->isSuccess();
    }

    /**
     * Extract an error message for a non-success HTTP status.
     *
     * Returns null for 2xx statuses (body usability is checked separately).
     * For transport failures (status 0), returns the body text or a default message.
     * For other non-2xx statuses, prefers JSON fields: message, messages, error_description, error;
     * otherwise the status code string.
     */
    public static function extractErrorMessage(string $body, int $statusCode): ?string
    {
        if (self::isHttpSuccessStatus($statusCode)) {
            return null;
        }

        if ($statusCode === 0) {
            return $body !== '' ? $body : 'Transport error';
        }

        $message = (string) $statusCode;

        if ($body !== '' && json_validate($body)) {
            $decodedBody = json_decode($body, true);
            if (is_array($decodedBody)) {
                foreach (['message', 'messages', 'error_description', 'error'] as $field) {
                    $text = isset($decodedBody[$field]) ? self::errorFieldToString($decodedBody[$field]) : null;
                    if ($text !== null && $text !== '') {
                        $message = $text;
                        break;
                    }
                }
            }
        }

        return $message;
    }

    /**
     * Turn a JSON error field into a readable message.
     *
     * A list of scalars is joined with "; ". Any other array or object is
     * JSON-encoded instead of being cast to the string "Array".
     */
    private static function errorFieldToString(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (!is_array($value)) {
            return null;
        }

        if (array_is_list($value) && $value !== []) {
            $parts = array_filter($value, fn(mixed $item) => is_string($item) || is_int($item) || is_float($item));
            if (count($parts) === count($value)) {
                return implode('; ', array_map('strval', $parts));
            }
        }

        $encoded = json_encode($value);
        return $encoded === false ? null : $encoded;
    }

    /**
     * Whether a decoded JSON list contains an entry that is not an object or array.
     *
     * @param array<mixed> $items
     */
    public static function hasNonArrayItem(array $items): bool
    {
        foreach ($items as $item) {
            if (!is_array($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * For a 2xx response, decide if the body is usable for JSON parsing.
     *
     * @param bool $allowEmpty When true (list endpoints), empty/whitespace body is usable.
     *                         When false (single-resource JSON endpoints), empty body is an error.
     * A non-empty body must decode to a JSON object or array; a bare string,
     * number, boolean, or null is not usable.
     *
     * @return string|null Error message when the body is not usable; null when parsing may proceed
     */
    public static function unusableSuccessBodyMessage(string $body, bool $allowEmpty = true): ?string
    {
        if (trim($body) === '') {
            return $allowEmpty ? null : 'Empty response body';
        }

        if (!json_validate($body)) {
            return 'Invalid JSON response';
        }

        if (!is_array(json_decode($body, true))) {
            return 'Unexpected JSON response';
        }

        return null;
    }
}
