<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\Tests\OpenApi;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Jamarcer\OpenApiMessagingContext\OpenApi\OpenApiSchemaParser;

class OpenApiSchemaParserTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function given_valid_v30_schema_when_parse_then_get_parsed_schema(): void
    {
        $allSpec = Yaml::parse(file_get_contents(__DIR__ . '/valid-openapi-v30-spec.yaml'));
        $schema = (new OpenApiSchemaParser($allSpec))->parse('Pet');
        $jsonCompleted = OpenApiSchemaMother::sampleSchema();
        self::assertJsonStringEqualsJsonString(json_encode($schema), $jsonCompleted);
    }

    /**
     * @test
     * @group unit
     */
    public function given_valid_v30_schema_when_parse_non_existent_schema_then_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Schema with name <non.existent.schema> not found');
        $allSpec = Yaml::parse(file_get_contents(__DIR__ . '/valid-openapi-v30-spec.yaml'));
        (new OpenApiSchemaParser($allSpec))->parse('non.existent.schema');
    }

    /**
     * @test
     * @group unit
     */
    public function given_valid_v30_schema_when_parse_from_path_then_get_parsed_schema(): void
    {
        $allSpec = Yaml::parse(file_get_contents(__DIR__ . '/valid-openapi-v30-spec.yaml'));
        $schema = (new OpenApiSchemaParser($allSpec))
            ->fromResponse('/pets', 'get', 200, 'application/json');
        $jsonCompleted = OpenApiSchemaMother::samplePath();
        self::assertJsonStringEqualsJsonString(json_encode($schema), $jsonCompleted);
    }
}
