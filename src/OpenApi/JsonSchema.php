<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\OpenApi;

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use RuntimeException;

final class JsonSchema
{
    private string $schema;
    private ?string $uri;

    public function __construct(string $schema, ?string $uri = null)
    {
        $this->schema = $schema;
        $this->uri = $uri;
    }

    public function resolve(SchemaStorage $resolver): JsonSchema
    {
        if (!$this->hasUri()) {
            return $this;
        }

        $this->schema = $resolver->resolveRef($this->uri);

        return $this;
    }

    public function validate($json, Validator $validator): bool
    {
        $validator->check($json, $this->schema);

        if (!$validator->isValid()) {
            $msg = "JSON does not validate. Violations:".\PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf("  - [%s] %s".\PHP_EOL, $error['property'], $error['message']);
            }
            throw new RuntimeException($msg);
        }

        return true;
    }

    public function schema(): string
    {
        return $this->schema;
    }

    private function hasUri(): bool
    {
        return null !== $this->uri;
    }
}