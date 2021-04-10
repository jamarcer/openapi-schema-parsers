<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\OpenApi;

use InvalidArgumentException;

final class V30SchemaExtractor implements SchemaExtractor
{
    public function extract(array $originalContent, string $schema): array
    {
        if (false === array_key_exists($schema, $originalContent['components']['schemas'])) {
            throw new InvalidArgumentException(sprintf('Schema with name <%s> not found', $schema));
        }

        return $originalContent['components']['schemas'][$schema];
    }
}