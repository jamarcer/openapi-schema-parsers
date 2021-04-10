<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Exception;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Jamarcer\OpenApiMessagingContext\OpenApi\JsonSchema;
use Jamarcer\OpenApiMessagingContext\OpenApi\OpenApiSchemaParser;
use function file_get_contents;
use function is_file;
use function json_decode;
use function json_encode;
use function realpath;
use function strtolower;

final class ResponseValidatorOpenApiContext implements Context
{
    private $minkContext;
    private $rootPath;

    public function __construct(string $schemaPath)
    {
        $this->rootPath = $schemaPath;
    }

    /**
     * @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function bootstrapEnvironment(BeforeScenarioScope $scope): void
    {
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
    }

    /**
     * @Then the JSON response should be valid according to OpenApi :dumpPath schema :schema
     */
    public function theJsonResponseShouldBeValidAccordingToOpenApiSchema($dumpPath, $schema): void
    {
        $path = realpath($this->rootPath . '/' . $dumpPath);
        $this->checkSchemaFile($path);

        $responseJson = $this->minkContext->getSession()->getPage()->getContent();

        $allSpec = Yaml::parse(file_get_contents($path));
        $schemaSpec = (new OpenApiSchemaParser($allSpec))->parse($schema);

        $this->validate($responseJson, new JsonSchema(json_decode(json_encode($schemaSpec), false)));
    }

    /**
     * @Then the response should be valid according to OpenApi :dumpPath with path :openApiPath
     */
    public function theResponseShouldBeValidAccordingToOpenApiWithPath(string $dumpPath, string $openApiPath): void
    {
        $path = realpath($this->rootPath . '/' . $dumpPath);
        $this->checkSchemaFile($path);

        $statusCode = $this->extractStatusCode();
        $method = $this->extractMethod();
        $contentType = $this->extractContentType();

        $responseJson = $this->minkContext->getSession()->getPage()->getContent();

        $allSpec = Yaml::parse(file_get_contents($path));
        $schemaSpec = (new OpenApiSchemaParser($allSpec))->fromResponse($openApiPath, $method, $statusCode, $contentType);

        $this->validate($responseJson, new JsonSchema(json_encode($schemaSpec)));
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

    private function extractMethod(): string
    {
        /** @var Client $requestClient */
        $requestClient = $this->minkContext->getSession()->getDriver()->getClient();

        try {
            $method = $requestClient->getHistory()->current()->getMethod();
        } catch (Exception $caught) {
            $method = $requestClient->getRequest()->getMethod();
        }

        return strtolower($method);
    }

    private function extractStatusCode(): int
    {
        return $this->minkContext->getSession()->getStatusCode();
    }

    private function extractContentType(): string
    {
        return $this->minkContext->getSession()->getResponseHeader('content-type');
    }
}