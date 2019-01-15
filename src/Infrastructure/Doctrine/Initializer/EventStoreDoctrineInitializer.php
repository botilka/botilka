<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

final class EventStoreDoctrineInitializer implements StoreInitializer
{
    private $connection;
    private $tableName;

    public function __construct(Connection $connection, string $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function initialize(bool $force = false): void
    {
        $tableName = $this->tableName;
        $connection = $this->connection;
        $schemaManager = $connection->getSchemaManager();
        $schema = new Schema();

        if (true === $force && $schemaManager->tablesExist([$tableName])) {
            $schemaManager->dropTable($tableName);
        }

        /** @var Table $table */
        $table = $schema->createTable($tableName);
        $table->addColumn('id', 'uuid');
        $table->addColumn('playhead', 'integer', ['unsigned' => true]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('payload', 'json');
        $table->addColumn('metadata', 'json');
        $table->addColumn('recorded_on', 'datetime_immutable');
        $table->addColumn('domain', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id', 'playhead']);
        $table->addIndex(['domain']);

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

    public function getType(): string
    {
        return StoreInitializer::TYPE_EVENT_STORE;
    }
}
