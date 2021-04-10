<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\Tests\AsyncApi;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Jamarcer\OpenApiMessagingContext\AsyncApi\AsyncApiSchemaParser;
use function json_encode;

class AsyncApiSchemaParserTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function given_valid_v20_schema_when_parse_then_get_parsed_schema(): void
    {
        $allSpec = Yaml::parse(file_get_contents(__DIR__ . '/valid-asyncapi-v20-spec.yaml'));
        $schema = (new AsyncApiSchemaParser($allSpec))->parse('cna.test.testtopic');
        $jsonCompleted = AsyncApiSchemaMother::sample();
        self::assertJsonStringEqualsJsonString(json_encode($schema), $jsonCompleted);
    }

    /**
     * @test
     * @group unit
     */
    public function given_valid_v20_schema_when_parse_non_existent_topic_then_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Topic with name <non.existent.topic> not found');
        $allSpec = Yaml::parse(file_get_contents(__DIR__ . '/valid-asyncapi-v20-spec.yaml'));
        (new AsyncApiSchemaParser($allSpec))->parse('non.existent.topic');
    }
}
