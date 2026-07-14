<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Request;

use GlobalLink\RestClient\Exception\InvalidArgumentException;

/**
 * Request for uploading a source file to a submission.
 *
 * Provide either file path OR (fileContents + fileName).
 */
final readonly class UploadSourceFileRequest
{
    /**
     * @param string $batchName Batch name (required)
     * @param string|null $file File path (alternative to fileContents/fileName)
     * @param string|null $fileContents File contents (alternative to file path)
     * @param string|null $fileName File name (required when using fileContents)
     * @param list<string>|null $targetLanguages Target languages
     * @param array<string, string>|null $metadata Metadata
     */
    public function __construct(
        public string $batchName,
        public ?string $file = null,
        public ?string $fileContents = null,
        public ?string $fileName = null,
        public ?bool $extractArchive = null,
        public ?string $fileFormatName = null,
        public ?array $targetLanguages = null,
        public ?array $metadata = null,
        public ?bool $overwrite = null,
        public ?string $clientIdentifier = null,
        public ?bool $removeFolders = null,
        public ?bool $returnTargets = null,
        public ?bool $useDefaultWorkflow = null,
    ) {
        if (empty(trim($batchName))) {
            throw new InvalidArgumentException('Batch name cannot be empty');
        }
        $hasFile = $file !== null && $file !== '';
        $hasContents = $fileContents !== null && $fileName !== null && $fileName !== '';
        if (!$hasFile && !$hasContents) {
            throw new InvalidArgumentException('Provide either file path or fileContents with fileName');
        }
        if ($hasFile && $hasContents) {
            throw new InvalidArgumentException('Provide either file path OR fileContents with fileName, not both');
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
        $data = [
            [
                'name' => 'batchName',
                'contents' => $this->batchName,
            ],
        ];

        if ($this->file !== null && $this->file !== '') {
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
            $data[] = [
                'name' => 'file',
                'contents' => fopen($realPath, 'r'),
                'filename' => basename($realPath),
            ];
        } else {
            $safeFilename = basename($this->fileName ?? '');
            if (empty($safeFilename)) {
                throw new InvalidArgumentException("Invalid filename provided");
            }
            $data[] = [
                'name' => 'file',
                'contents' => $this->fileContents ?? '',
                'filename' => $safeFilename,
            ];
        }

        if ($this->extractArchive !== null) {
            $data[] = [
                'name' => 'extractArchive',
                'contents' => $this->extractArchive ? 'true' : 'false',
            ];
        }
        if ($this->fileFormatName !== null) {
            $data[] = [
                'name' => 'fileFormatName',
                'contents' => $this->fileFormatName,
            ];
        }
        if ($this->targetLanguages !== null) {
            $data[] = [
                'name' => 'targetLanguages',
                'contents' => implode(',', $this->targetLanguages),
            ];
        }
        if ($this->metadata !== null && is_array($this->metadata)) {
            foreach ($this->metadata as $key => $value) {
                $data[] = [
                    'name' => "metadata[$key]",
                    'contents' => $value,
                ];
            }
        }
        if ($this->overwrite !== null) {
            $data[] = [
                'name' => 'overwrite',
                'contents' => $this->overwrite ? 'true' : 'false',
            ];
        }
        if ($this->clientIdentifier !== null) {
            $data[] = [
                'name' => 'clientIdentifier',
                'contents' => $this->clientIdentifier,
            ];
        }
        if ($this->removeFolders !== null) {
            $data[] = [
                'name' => 'removeFolders',
                'contents' => $this->removeFolders ? 'true' : 'false',
            ];
        }
        if ($this->returnTargets !== null) {
            $data[] = [
                'name' => 'returnTargets',
                'contents' => $this->returnTargets ? 'true' : 'false',
            ];
        }
        if ($this->useDefaultWorkflow !== null) {
            $data[] = [
                'name' => 'useDefaultWorkflow',
                'contents' => $this->useDefaultWorkflow ? 'true' : 'false',
            ];
        }

        return $data;
    }
}
