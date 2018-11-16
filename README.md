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
- Replay all or some events (allow to test domain changes).
- Rebuild a projection on demand (ie. when you add a ReadModel).
- Safe commands concurrency.
- Fully immutable, not a single setter.
- Tested, good code coverage. 
- EventSourced and CQRS repositories available.

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

Botilka provide a command to create & configure the event store:

```sh
bin/console botilka:event_store:initialize doctrine # or 'mongodb'
```
you can force recreate, but be carefull, you will lost all the previous events:
```sh
bin/console botilka:event_store:initialize doctrine -f # or 'mongodb'
```

## Usage

### CQRS & EventSourcing

You'll need to create Commands, Queries, Events and so on. [Read the documentation](/documentation/cqrs.md).

### Event replaying

It you've added or changed a business rule, you may want to see how it would have behaved with your event stream,
this is a use case for event replaying.

Let's say the BI team said they want to send a SMS each time withdrawal is made, so you have to:
1. create the new event handler
2. re-play events

The event
```php
<?php
final class SendPostalCardOnBankAccountCreated implements EventHandler
{
    public function onWithdrawalPerformed(WithdrawalPerformed $event): void
    {
        $user = $this->userRepository->getOwner($event->getgetAccountId());
        if ($this->isMobilePhone($phoneNumber = $user->getPhoneNumber())) {
            // record the calls count somewhere, now you know how many SMS would have been sent
            $this->smsSender->send($phoneNumber);
        }
    }
}
```

Replay:
```bash
# you can limit the scope with --from/-f & --to/-t
bin/console botilka:event_store:replay [aggregate root id] --from 150
```

### Projection replay

In the same way than replaying events, you can replay projection. If you've added a projection
and you want to replay only these projection, use the `--matching/-m` options.

> Matching is a regex matched against \[ProjectFQCN\]::\[method\],
> ie. `App\BankAccount\Projection\Doctrine\BankAccountProjector::sumOfDeposit`

The projection
```php
<?php
namespace App\BankAccount\Projection\Doctrine;

final class BankAccountProjector implements Projector
{
    public function sumOfDeposit(DepositPerformed $event): void
    {
        $stmt = $this->connection->prepare('UPDATE all_the_sums SET value = value + :amount WHERE type = :type');
        $stmt->prepare(['amount' => $event->getAmount(), 'type' => 'deposit']);
        $stmt->execute();
    }

    public static function getSubscribedEvents()
    {
        return [
            onWithdrawalPerformed::class => 'onWithdrawalPerformed',
        ];
    }
}
```

Replay projection:
```bash
# you can limit the scope with --from/-f & --to/-t
bin/console botilka:projector:replay [aggregate root id] --matching sumOfDeposit
```


### API Platform bridge
See the [API Platform bridge](/documentation/api_platform_bridge.md) documentation.

## Testing

This project uses PHP Unit: `vendor/bin/phpunit`.

Functionals tests are grouped under the tag `functional`: `vendor/bin/phpunit --group functional`. 

## How it works

Have a look [here](/documentation/internals.md) to better understand the design choices made and how the magic stuff happens.

### todo

- Snapshots.
- Raw events iterator & modifiers (for updatating events).
- Add domain concept to event store.
- (maybe) Process manager.
- (maybe) Smart command retry on concurrency exception.


### Resources

- https://github.com/dddinphp/blog-cqrs
- https://github.com/broadway/broadway
- https://github.com/jorge07/symfony-4-es-cqrs-boilerplate (uses Broadway)
- https://github.com/CodelyTV/cqrs-ddd-php-example
- https://github.com/mnavarrocarter/ddd
- https://www.youtube.com/watch?v=qBLtZN3p3FU \[french\]
- https://www.youtube.com/watch?v=VpzSMz_XbqM \[french\]
