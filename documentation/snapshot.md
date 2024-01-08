# Snapshot

When you have a lot of events, it could became slow to retrieve an event sourced aggregate root.
By using snapshot, you just have to replay a few event on top of it.

## How it works

For using snapshot, you need to declare a "standard" repository for an aggregate root then decorate it with
the *snapshot-activated* repository.

The repositories needs to be in a registry for the [`EventDispatcherMiddleware`](/src/Infrastructure/Symfony/Messenger/Middleware/EventDispatcherMiddleware.php) to be able to use it when saving an event.
By implementing the `EventSourcedRepository` interface, it is transparent for `EventDispatcherMiddleware` that it's using
a repository with snapshot availability.

When saving an event, the [`SnapshotedEventSourcedRepository`](/src/Snapshot/SnapshotedEventSourcedRepository.php) calls a [`SnapshotStrategist`](/src/Snapshot/Strategist/SnapshotStrategist.php)
that choose to make a snapshot or not.

When retrieving an aggregate, the repository will try to load from the snapshot store or fallback to the "standard" repository.


## Setup

As for event store, Botilka provide a command to create & configure the snapshot store:

```sh
bin/console botilka:store:initialize snapshot doctrine # or mongodb
```
You can force recreate, but be carefull, you will lost all the previous snapshots:
```sh
bin/console botilka:store:initialize snapshot doctrine -f
```


## Configuration

```yaml
services:

    # "standard" repository
    app.repository.bank_account:
        class: 'Botilka\Repository\DefaultEventSourcedRepository'
        arguments:
            $aggregateRootClassName: 'App\BankAccount\Domain\BankAccount'

    # "snapshot" repository that decorates the standard one
    app.repository.bank_account.snapshot:
        decorates: 'app.repository.bank_account'
        class: 'Botilka\Snapshot\SnapshotedEventSourcedRepository'

    # registry configuration
    Botilka\Repository\DefaultEventSourcedRepositoryRegistry:
        arguments:
            $repositories:
                App\BankAccount\Domain\BankAccount: '@app.repository.bank_account'

    # your domain repository now uses the Botilka repository, not the event store directly anymore
    App\BankAccount\Infrastructure\Repository\BankAccountEventStoreRepository:
        arguments:
            $repository: '@app.repository.bank_account' # you need to inject the standard one, Symfony DI will decorate it
```

## Snapshot strategy

Create you own strategist implementing [`SnapshotStrategist`](/src/Snapshot/Strategist/SnapshotStrategist.php)

```php
<?php

namespace App\EventSourcing\Snapshot\Strategist;

use Botilka\Snapshot\Strategist\SnapshotStrategist;

class RandomSnapshotStrategist implements SnapshotStrategist {

    public function mustSnapshot(EventSourcedAggregateRoot $aggregateRoot): bool
    {
        return rand(0, 10) === 5; // we don't care of $aggregateRoot, it's just random
    }
}
```

Inject it to the repository managing your aggregate:
```yaml
services:

    App\EventSourcing\Snapshot\Strategist\RandomSnapshotStrategist: ~

    app.repository.bank_account.snapshot:
        decorates: 'app.repository.bank_account'
        class: 'Botilka\Snapshot\SnapshotedEventSourcedRepository'
        arguments:
            $strategist: '@App\EventSourcing\Snapshot\Strategist\RandomSnapshotStrategist'
```
