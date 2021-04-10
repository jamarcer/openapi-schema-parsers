<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\AsyncApi;

use InvalidArgumentException;
use Jamarcer\Topic\Topic;
use function array_key_exists;
use function sprintf;

final class V20ChannelExtractor implements ChannelExtractor
{
    public function extract(array $originalContent, string $channel): array
    {
        // Validar SCOPE
        $channel = $this->channelParser($channel);

        if (false === array_key_exists($channel, $originalContent['channels'])) {
            throw new InvalidArgumentException(sprintf('Topic with name <%s> not found', $channel));
        }

        return $originalContent['channels'][$channel]['subscribe']['message'];
    }

    private function channelParser(string $channel): string
    {
        $components = Topic::toArray($channel);
        if (('domain_event' === $components['type'])
            && in_array($components['scope'], ['world', 'andres', 'euromontyres', 'motoval']))
        {
            $components['scope'] = 'SCOPE';
        }
        return Topic::from($components);
    }
}