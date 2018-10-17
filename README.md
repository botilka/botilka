# BOTILKA

An modern & easy-to-use Event Sourcing & CQRS library framework. It's shipped with implementations built on top of Symfony components.

It can leverage [API Platform](https://api-platform.com) to expose the `Commands` and `Queries` via REST.

[![Build Status](https://travis-ci.org/botilka/botilka.svg?branch=master)](https://travis-ci.org/botilka/botilka)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/botilka/botilka/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/botilka/botilka/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)

## Features

- Fully immutable, not a single setter.
- Sync or async event handling is a matter of configuration.
- Replay all or some events.
- Safe commands concurrency.
- EventSourced aggregates not mandatory.
- *(optionnal)* EventStore persisted with Doctrine.
- *(optionnal)* Commands/queries handling & description on API Platform UI.
- *(optionnal)* Read-only projections managed with Doctrine, easy to migrate.


## todo

- Snapshots.
- One endpoint by command/query.
- EventStore in MongoDB / Redis
- ReadModel Elasticsearch
- (maybe) Smart command retry on concurrency exception.

## Documentation

- [API Platform bridge](/documentation/api_platform_bridge.md)
- See below.

## Usage

### Messages & Buses

Messages are just POPO objects.
All 3 (`command`, `query` & `event`) buses are (by default) managed by [Symfony Messenger Component](https://symfony.com/doc/4.1/messenger.html) buses,
so it's pretty easy to go async on event handling or add any middleware.

Messages & handlers just have to implement an empty interface and everything is automatically wired using auto-configuration.

### Commands & Queries

#### Handling

Handlers get their dependencies (collaborators) using constructor injection:
- `CommandHandler` need an Repository (event sourced or not)
- `QueryHandler` & `EventHandler` use whatever collaborator you want

*Sample command & handler*
```php
// src/TheDomain/Command/TheCommand.php
namespace App\TheDomain\Command;

use Botilka\Application\Command\Command;

final class TheCommand implements Command {
    
    private $modelId;
    private $what;
    
    public function __construct(string $modelId, string $what)
    {
        $this->modelId = $modelId;
        $this->what = $what;
    }
    
    // add getters
}

// src/TheDomain/Command/TheCommandHandler.php
namespace App\TheDomain\Command;

use Botilka\Application\Command\CommandHandler;

final class TheCommandHandler implements CommandHandler {

    private $repository;
    
    public function __construct(TheEventSourcedRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(TheCommand $command): CommandResponse
    {
        $theDomainModel = $this->repository->get($command->getModelId());
        /** @var DomainModel $instance */
        [$instance, $event] = $theDomainModel->makeSomething($command->getWhat());

        return CommandResponse::withValue($instance->getAggregateRootId(), $instance->getPlayhead(), $event);
    }
}
```
