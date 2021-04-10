<?php
declare(strict_types=1);

namespace Jamarcer\OpenApiMessagingContext\Tests\OpenApi;

final class OpenApiSchemaMother
{
    public static function sampleSchema(): string
    {
        $content = [
            "required" => [
                "id",
                "name"
            ],
            "properties" => [
                "id" => [
                    "type" => "integer",
                    "format" => "int64"
                ],
                "name" => [
                    "type" => "string"
                ],
                "tag" => [
                    "type" => "string"
                ]
            ]
        ];
        return json_encode($content);
    }

    public static function samplePath(): string
    {
        $content = [
            "type" => "array",
            "items" => [
                "required" => [
                    "id",
                    "name"
                ],
                "properties" => [
                    "id" => [
                        "type" => "integer",
                        "format" => "int64"
                    ],
                    "name" => [
                        "type" => "string"
                    ],
                    "tag" => [
                        "type" => "string"
                    ]
                ]
            ]
        ];
        return json_encode($content);
    }
}