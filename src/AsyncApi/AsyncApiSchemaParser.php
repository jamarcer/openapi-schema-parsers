<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\AsyncApi;

use InvalidArgumentException;
use RuntimeException;
use Jamarcer\OpenApiMessagingContext\Shared\BaseSchemaParser;
use function array_key_exists;
use function preg_match;
use function sprintf;

final class AsyncApiSchemaParser extends BaseSchemaParser
{
    private $versionExtractor;

    /**
     * SchemaParser constructor.
     * @param $schemaContent
     */
    public function __construct(array $schemaContent)
    {
        parent::__construct($schemaContent);
        $this->versionExtractor = [
            '2.0' => new V20ChannelExtractor()
        ];
    }

    public function parse($name): array
    {
        $channelExtractor = $this->extractVersion();

        return $this->extractData($channelExtractor->extract($this->schemaContent, $name));
    }

    private function extractVersion(): ChannelExtractor
    {
        if (false === array_key_exists('asyncapi', $this->schemaContent)) {
            throw new RuntimeException('Unable to find asyncapi document version');
        }

        if (1 === preg_match('/^2\.0/', $this->schemaContent['asyncapi'])) {
            return $this->versionExtractor['2.0'];
        }

        throw new InvalidArgumentException(
            sprintf('%s async api version not supported', $this->schemaContent['asyncapi'])
        );
    }
}