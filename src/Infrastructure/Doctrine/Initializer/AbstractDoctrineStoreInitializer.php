<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine\Initializer;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Schema;

abstract class AbstractDoctrineStoreInitializer
{
    private $connection;
    protected $tableName;

    public function __construct(Connection $connection, string $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    protected function doInitialize(Schema $schema, bool $force): void
    {
        $tableName = $this->tableName;
        $connection = $this->connection;
        $schemaManager = $connection->getSchemaManager();

        if (true === $force && $schemaManager->tablesExist([$tableName])) {
            $schemaManager->dropTable($tableName);
        }

        $sql = $schema->toSql($connection->getDatabasePlatform());

        $connection->beginTransaction();

        try {
            foreach ($sql as $query) {
                $connection->exec($query);
            }
            $connection->commit();
        } catch (TableExistsException $e) {
            $connection->rollBack();
            throw new \RuntimeException($e->getMessage());
        }
    }
}
