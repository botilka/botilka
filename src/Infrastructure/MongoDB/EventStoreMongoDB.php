<?php

namespace Botilka\Infrastructure\MongoDB;

use Botilka\Event\Event;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use MongoDB\Collection;
final class EventStoreMongoDB implements EventStore
{

    /** @var Client */
    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function load(string $id): array
    {
        // TODO: Implement load() method.
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        // TODO: Implement loadFromPlayhead() method.
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        // TODO: Implement loadFromPlayheadToPlayhead() method.
    }

    public function append(string $id, int $playhead, string $type, Event $payload, ?array $metadata, \DateTimeImmutable $recordedOn) {

//        $collection->insertOne(['foo' => 'dqsq']);

        dump($this->collection->find()->toArray());
    }


}
