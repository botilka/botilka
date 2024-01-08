<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

final readonly class SnapshotStoreDoctrineInitializer extends AbstractDoctrineStoreInitializer implements StoreInitializer
{
    public function initialize(bool $force = false): void
    {
        $schema = new Schema();

        /** @var Table $table */
        $table = $schema->createTable($this->tableName);
        $table->addColumn('id', 'uuid');
        $table->addColumn('playhead', 'integer', ['unsigned' => true]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('payload', 'json');
        $table->setPrimaryKey(['id']);

        $this->doInitialize($schema, $force);
    }

    public function getType(): string
    {
        return StoreInitializer::TYPE_SNAPSHOT_STORE;
    }
}
