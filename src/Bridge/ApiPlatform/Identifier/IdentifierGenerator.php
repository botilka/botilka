<?php

namespace Botilka\Bridge\ApiPlatform\Identifier;

use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Doctrine\Common\Inflector\Inflector;

final class IdentifierGenerator
{
    private const PART_NAME_TO_REMOVE = [
        Command::class => 'Command',
        Query::class => 'Query',
    ];

    public static function generate(string $type, string $className): string
    {
        return Inflector::tableize(\str_replace([self::PART_NAME_TO_REMOVE[$className], '\\'], '', $type));
    }
}
