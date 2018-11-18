<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Application\EventStore\EventStoreInitializer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

final class EventStoreDoctrineInitializer implements EventStoreInitializer
{
    private $connection;
    private $table;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function initialize(bool $force = false): void
    {
        $table = $this->table;
        $connection = $this->connection;
        $schemaManager = $connection->getSchemaManager();
        $schema = new Schema();

        if (true === $force && $schemaManager->tablesExist([$table])) {
            $schemaManager->dropTable($table);
        }

        /** @var Table $table */
        $table = $schema->createTable($table);
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
}
