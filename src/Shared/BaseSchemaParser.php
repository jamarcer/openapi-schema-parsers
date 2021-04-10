<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\Shared;

use function array_key_exists;
use function array_reduce;
use function explode;
use function is_array;
use function preg_replace;

abstract class BaseSchemaParser
{
    protected $schemaContent;

    /**
     * SchemaParser constructor.
     * @param $schemaContent
     */
    public function __construct(array $schemaContent)
    {
        $this->schemaContent = $schemaContent;
    }

    protected function extractData(array $data): array
    {
        $aux = [];
        foreach ($data as $key => $elem) {
            if ('$ref' === $key) {
                $aux = $this->findDefinition($elem);
                continue;
            }
            if (is_array($elem)) {
                $aux[$key] = $this->extractData($elem);
                continue;
            }
            $aux[$key] = $elem;
        }

        return $aux;
    }

    protected function findDefinition(string $def): array
    {
        $cleanDef = preg_replace('/^\#\//', '', $def);
        $explodedDef = explode('/', $cleanDef);
        $foundDef = array_reduce($explodedDef, function ($last, $elem) {
            return null === $last ? $this->schemaContent[$elem] : $last[$elem];
        });

        return $this->extractData(array_key_exists('payload', $foundDef) ? $foundDef['payload'] : $foundDef);
    }
}