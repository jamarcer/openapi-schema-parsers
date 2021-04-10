<?php

declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\Behat;

use Behat\Behat\Context\Context;
use Exception;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Jamarcer\OpenApiMessagingContext\AsyncApi\AsyncApiSchemaParser;
use Jamarcer\OpenApiMessagingContext\Messaging\SpyMiddleware;
use Jamarcer\OpenApiMessagingContext\OpenApi\JsonSchema;
use function json_decode;
use function json_encode;

final class MessageValidatorOpenApiContext implements Context
{
    private string $rootPath;
    private SpyMiddleware $spyMiddleware;

    public function __construct(string $rootPath, SpyMiddleware $spyMiddleware)
    {
        $this->rootPath = $rootPath;
        $this->spyMiddleware = $spyMiddleware;
    }

    /**
     * @BeforeScenario
     */
    public function bootstrapEnvironment(): void
    {
        $this->spyMiddleware->reset();
    }

    /**
     * @Then the published message :name should be valid according to swagger :dumpPath
     */
    public function theMessageShouldBeValidAccordingToTheSwagger($name, $dumpPath): void
    {
        $path = realpath($this->rootPath . '/' . $dumpPath);
        $this->checkSchemaFile($path);

        $eventJson = $this->spyMiddleware->getMessage($name);

        $allSpec = Yaml::parse(file_get_contents($path));
        $schema = (new AsyncApiSchemaParser($allSpec))->parse($name);

        $this->validate($eventJson, new JsonSchema(json_encode($schema)));
    }

    /**
     * @Then the message :name should be dispatched
     */
    public function theMessageShouldBeDispatched(string $name): void
    {
        if (false === $this->spyMiddleware->hasMessage($name)) {
            throw new Exception(sprintf('Message %s not dispatched', $name));
        }
    }

    private function checkSchemaFile($filename): void
    {
        if (false === is_file($filename)) {
            throw new RuntimeException(
                'The JSON schema doesn\'t exist'
            );
        }
    }

    private function validate(string $json, JsonSchema $schema): bool
    {
        $validator = new Validator();

        $resolver = new SchemaStorage(new UriRetriever(), new UriResolver());
        $schema->resolve($resolver);

        return $schema->validate(json_decode($json, false), $validator);
    }
}