<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

final readonly class EventStoreDoctrineInitializer extends AbstractDoctrineStoreInitializer implements StoreInitializer
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
        $table->addColumn('metadata', 'json');
        $table->addColumn('recorded_on', 'datetime_immutable');
        $table->addColumn('domain', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id', 'playhead']);
        $table->addIndex(['domain']);

        $this->doInitialize($schema, $force);
    }

    public function getType(): string
    {
        return StoreInitializer::TYPE_EVENT_STORE;
    }
}
