<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\AsyncApi;

interface ChannelExtractor
{
    public function extract(array $originalContent, string $channel): array;
}