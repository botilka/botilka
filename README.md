# BOTILKA

[![Build Status](https://travis-ci.org/botilka/botilka.svg?branch=master)](https://travis-ci.org/botilka/botilka)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/botilka/botilka/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/botilka/botilka/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

An modern & easy-to-use Event Sourcing & CQRS library framework. It's shipped with implementations built on top of Symfony components.

It can leverage [API Platform](https://api-platform.com) to expose the `Commands` and `Queries` via REST.

## Features

- EventStore implementation with [Doctrine](https://www.doctrine-project.org/) or [MongoDB](https://www.mongodb.com).
- Commands/queries handling & description on API Platform UI.
- Sync or async event handling is a matter of configuration.
- Replay all or some events.
- Safe commands concurrency.
- Fully immutable, not a single setter, and fully typed.
- Tested, 100% code coverage. 
- EventSourced or CQRS repositories available.

## Configuration

An event store should (must) be persisted and the default implementation is not! \
Choose between:
 - `Botilka\Infrastructure\Doctrine\EventStoreDoctrine`
 - `Botilka\Infrastructure\MongoDB\EventStoreMongoDB`
 
```yaml
# config/packages/botilka.yaml
botilka:
    
    # default implementation is 'Botilka\Infrastructure\InMemory\EventStoreInMemory', not persisted!!
    event_store: Botilka\Infrastructure\Doctrine\EventStoreDoctrine # or 'Botilka\Infrastructure\MongoDB\EventStoreMongoDB'
```

Botilka provide a command to create add unique index to the event store:

```sh
bin/console botilka:event_store:initialize doctrine # or 'mongodb'
```
you can force recreate:
```sh
bin/console botilka:event_store:initialize doctrine -f # or 'mongodb'
```

## Usage

### Api Platform bridge

See the [API Platform bridge](/documentation/api_platform_bridge.md) documentation.

### Command

Create a command:
```php
<?php
use Botilka\Application\Command\Command;

final class CreateBankAccountCommand implements Command
{
    private $name;
    private $currency;

    public function __construct(string $name, ?string $currency)
    {
        $this->name = $name;
        $this->currency = $currency;
    }
    // add getters
}
```

Create a command handler:
```php
<?php
namespace App\BankAccount\Application\Command;

use App\BankAccount\Domain\BankAccount;
use Botilka\Application\Command\CommandHandler;
use Botilka\Application\Command\CommandResponse;
use Ramsey\Uuid\Uuid;

final class CreateBankAccountHandler implements CommandHandler
{
    public function __invoke(CreateBankAccountCommand $command): CommandResponse
    {
        $id = Uuid::uuid4();
        /** @var BankAccount $instance */
        [$instance, $event] = BankAccount::create($id->toString(), $command->getName(), $command->getCurrency());
        return CommandResponse::withValue($instance->getAggregateRootId(), $instance->getPlayhead(), $event);
    }
}
```
Create a command handler:
```php
<?php

namespace App\BankAccount\Domain;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Domain\EventSourcedAggregateRootApplier;

final class BankAccount implements EventSourcedAggregateRoot
{
    use EventSourcedAggregateRootApplier;

    /** @var string */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $currency;
    /** @var int */
    private $balance;
    private $playhead = -1;

    protected $eventMap = [
        BankAccountCreated::class => 'bankAccountCreated',
    ];

    public static function create(string $id, string $name, string $currency): array
    {
        $instance = new self();
        $event = new BankAccountCreated($id, $name, $currency);
        return [$instance->apply($event), $event];
    }

    private function bankAccountCreated(BankAccountCreated $event): BankAccount
    {
        $instance = clone $this;
        $instance->id = $event->getId();
        $instance->name = $event->getName();
        $instance->currency = $event->getCurrency();
        $instance->balance = 0;
        return $instance;
    }
}
```
and the corresponding event:
```php
<?php

namespace App\BankAccount\Domain;

use Botilka\Event\Event;

final class BankAccountCreated implements Event
{
    private $id;
    private $name;
    private $currency;

    public function __construct(string $id, string $name, string $currency)
    {
        $this->id = $id;
        $this->name = $name;
        $this->currency = $currency;
    }
    // add getters
}

``` 
Then dispatch the command
```php
<?php
use Botilka\Application\Command\CommandBus;

$command = new CreateBankAccountCommand('account in $', 'DOL');

/** @var CommandBus $bus */
$bus = $container->get(CommandBus::class); // retrieve it by injection
$response = $bus->dispatch($command);

echo $response->getId(); // aggregate root id
```

### How it works

Each `Command`, `Query` & `Event` are just POPO. For all of them, we use the Bus pattern to dispatch and
handle these messages. They are all transported on their own bus.

Buses are (by default) managed by [Symfony Messenger Component](https://symfony.com/doc/4.1/messenger.html).

Messages & handlers just have to implement an empty interface and everything is automatically wired
using auto-configuration.

The matching between a message and it(s) handler(s) is done by the Messenger component.
> The handler has an `__invoke` method with the type hinted message as the sole argument.


### todo

- Snapshots.
- Projectors.
- (maybe) Process manager.
- (maybe) Smart command retry on concurrency exception.


### Resources

- https://github.com/dddinphp/blog-cqrs
- https://github.com/broadway/broadway
- https://github.com/CodelyTV/cqrs-ddd-php-example
- https://github.com/jorge07/symfony-4-es-cqrs-boilerplate
- https://github.com/mnavarrocarter/ddd
- https://www.youtube.com/watch?v=qBLtZN3p3FU \[french\]
- https://www.youtube.com/watch?v=VpzSMz_XbqM \[french\]
