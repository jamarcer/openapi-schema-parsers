<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\OpenApi;

use InvalidArgumentException;
use RuntimeException;
use Jamarcer\OpenApiMessagingContext\Shared\BaseSchemaParser;

final class OpenApiSchemaParser extends BaseSchemaParser
{
    private $versionExtractor;

    public function __construct(array $schemaContent)
    {
        parent::__construct($schemaContent);
        $this->versionExtractor = [
            '3.0' => new V30SchemaExtractor()
        ];
    }
    public function parse($name): array
    {
        $channelExtractor = $this->extractVersion();

        return $this->extractData($channelExtractor->extract($this->schemaContent, $name));
    }

    private function extractVersion(): SchemaExtractor
    {
        if (false === array_key_exists('openapi', $this->schemaContent)) {
            throw new RuntimeException('Unable to find openapi document version');
        }

        if (1 === preg_match('/^3\.0/', $this->schemaContent['openapi'])) {
            return $this->versionExtractor['3.0'];
        }

        throw new InvalidArgumentException(
            sprintf('%s open api version not supported', $this->schemaContent['openapi'])
        );
    }

    public function fromResponse(string $path, string $method, int $statusCode, string $contentType): array
    {
        $rootPaths = $this->schemaContent['paths'];
        $this->assertPathRoot($path, $rootPaths);
        $pathRoot = $rootPaths[$path];

        $this->assertMethodRoot($path, $method, $pathRoot);
        $methodRoot = $pathRoot[$method];

        $this->assertStatusCodeRoot($path, $method, $statusCode, $methodRoot);
        $statusCodeRoot = $methodRoot['responses'][$statusCode];

        if (false === array_key_exists('content', $statusCodeRoot)) {
            return [];
        }

        $this->assertContentTypeRoot($path, $method, $statusCode, $contentType, $statusCodeRoot);
        return $this->extractData($statusCodeRoot['content'][$contentType]['schema']);
    }

    private function assertPathRoot(string $path, $rootPaths): void
    {
        if (false === array_key_exists($path, $rootPaths)) {
            throw new InvalidArgumentException(sprintf('%s path not found', $path));
        }
    }

    private function assertMethodRoot(string $path, string $method, $pathRoot): void
    {
        if (false === array_key_exists($method, $pathRoot)) {
            throw new InvalidArgumentException(sprintf('%s method not found on %s', $method, $path));
        }
    }

    private function assertStatusCodeRoot(string $path, string $method, int $statusCode, $methodRoot): void
    {
        if (false === array_key_exists('responses', $methodRoot) || false === array_key_exists(
            $statusCode,
            $methodRoot['responses']
        )) {
            throw new InvalidArgumentException(
                sprintf('%s response not found on %s path with %s method', $statusCode, $path, $method)
            );
        }
    }

    private function assertContentTypeRoot(
        string $path,
        string $method,
        int $statusCode,
        string $contentType,
        $statusCodeRoot
    ): void {
        if (false === array_key_exists($contentType, $statusCodeRoot['content'])) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s content-type not found on %s path with %s method with %s statusCode',
                    $contentType,
                    $path,
                    $method,
                    $statusCode
                )
            );
        }
    }
}