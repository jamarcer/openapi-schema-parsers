<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\Tests\AsyncApi;

use function json_encode;

final class AsyncApiSchemaMother
{
    public static function sample(): string
    {
        $content = [
            "type" => "object",
            "required" => ["message_id","type"],
            "properties" => [
                "message_id" => ["type" => "string"],
                "type" => ["type" => "string"],
                "attributes" => [
                    "type" => "object",
                    "required" => ["some_attribute"],
                    "properties" => [
                        "some_attribute" => ["type" => "string"]
                    ]
                ]
            ]
        ];
        return json_encode($content);
    }
}