<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine\Initializer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Schema;

abstract readonly class AbstractDoctrineStoreInitializer
{
    public function __construct(
        private Connection $connection,
        protected string $tableName,
    ) {}

    protected function doInitialize(Schema $schema, bool $force): void
    {
        $tableName = $this->tableName;
        $connection = $this->connection;
        $schemaManager = $connection->getSchemaManager();

        if ($force && $schemaManager->tablesExist([$tableName])) {
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
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
