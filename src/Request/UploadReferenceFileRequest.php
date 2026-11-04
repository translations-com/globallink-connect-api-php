<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Request;

use GlobalLink\RestClient\Exception\InvalidArgumentException;

/**
 * Request for uploading a reference file to a submission.
 *
 * Provide either file path, fileContents with fileName, or a stream resource.
 */
final readonly class UploadReferenceFileRequest
{
    /**
     * @param resource|string|null $file File path or stream (optional if using fileContents/fileName)
     * @param string|null $fileContents File contents (alternative to file path)
     * @param string|null $fileName File name (required when using fileContents)
     * @param bool|null $submissionLevel Whether this is a submission-level reference
     * @param string|null $targetLanguageLevel Target language for language-level reference
     * @param bool|null $extractArchive Whether to extract archive
     *
     * @throws InvalidArgumentException If both submissionLevel and targetLanguageLevel are specified
     */
    public function __construct(
        public mixed $file = null,
        public ?string $fileContents = null,
        public ?string $fileName = null,
        public ?bool $submissionLevel = null,
        public ?string $targetLanguageLevel = null,
        public ?bool $extractArchive = null,
    ) {
        if ($submissionLevel === true && $targetLanguageLevel !== null && $targetLanguageLevel !== '') {
            throw new InvalidArgumentException(
                "Only one of submissionLevel and targetLanguageLevel can be specified."
            );
        }
        $hasFile = ($this->file !== null && (is_string($this->file) ? $this->file !== '' : true));
        $hasContents = $this->fileContents !== null && $this->fileName !== null && $this->fileName !== '';
        if (!$hasFile && !$hasContents) {
            throw new InvalidArgumentException(
                "Provide either file path, fileContents with fileName, or a stream resource"
            );
        }
    }

    /**
     * Convert to multipart/form-data format.
     *
     * @return list<array{name: string, contents: mixed, filename?: string}>
     * @throws InvalidArgumentException If file path is invalid or file not found
     */
    public function toMultipart(): array
    {
        $multipart = [];

        if (is_string($this->file) && $this->file !== '') {
            $realPath = realpath($this->file);
            if ($realPath === false) {
                throw new InvalidArgumentException("File not found or invalid path: " . $this->file);
            }
            if (!is_file($realPath)) {
                throw new InvalidArgumentException("Path is not a file: " . $this->file);
            }
            if (!is_readable($realPath)) {
                throw new InvalidArgumentException("File is not readable: " . $this->file);
            }
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($realPath, 'r'),
                'filename' => basename($realPath),
            ];
        } elseif ($this->fileContents !== null && $this->fileName !== null) {
            $safeFilename = basename($this->fileName);
            if (empty($safeFilename)) {
                throw new InvalidArgumentException("Invalid filename provided");
            }
            $multipart[] = [
                'name' => 'file',
                'contents' => $this->fileContents,
                'filename' => $safeFilename,
            ];
        } elseif ($this->file !== null && !is_string($this->file)) {
            $multipart[] = [
                'name' => 'file',
                'contents' => $this->file,
                'filename' => 'reference-file',
            ];
        } else {
            throw new InvalidArgumentException(
                "Provide either file path, fileContents with fileName, or a stream resource"
            );
        }

        if ($this->submissionLevel !== null) {
            $multipart[] = [
                'name' => 'submissionLevel',
                'contents' => $this->submissionLevel ? 'true' : 'false',
            ];
        }
        if ($this->targetLanguageLevel !== null) {
            $multipart[] = [
                'name' => 'targetLanguageLevel',
                'contents' => $this->targetLanguageLevel,
            ];
        }
        if ($this->extractArchive !== null) {
            $multipart[] = [
                'name' => 'extractArchive',
                'contents' => $this->extractArchive ? 'true' : 'false',
            ];
        }

        return $multipart;
    }
}
