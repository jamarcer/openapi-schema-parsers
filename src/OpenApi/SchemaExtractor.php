<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\OpenApi;

interface SchemaExtractor
{
    public function extract(array $originalContent, string $channel): array;
}