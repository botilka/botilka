# BOTILKA

[![Build Status](https://travis-ci.org/botilka/botilka.svg?branch=master)](https://travis-ci.org/botilka/botilka)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/botilka/botilka/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/botilka/botilka/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

An modern & easy-to-use Event Sourcing & CQRS library. It's shipped with implementations built on top of Symfony components.

It can leverage [API Platform](https://api-platform.com) to expose yours `Commands` and `Queries` via REST.

## Features

- EventStore implementation with [Doctrine](https://www.doctrine-project.org/) or [MongoDB](https://www.mongodb.com).
- Swagger commands & queries description (via API Platform UI).
- REST API access to commands & queries.
- Sync or async event handling is a matter of configuration.
- Event replaying (allow to test domain changes).
- Projection re-play on demand (ie. when you add a ReadModel).
- Safe commands concurrency (optimistic locking).
- Tested, good code coverage.

## Configuration

An event store should (must) be persisted and the default implementation is not! Choose between:
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
bin/console botilka:event_store:initialize
```
You can force recreate, but be carefull, you will lost all the previous events:
```sh
bin/console botilka:event_store:initialize -f
```

## Usage

### CQRS & EventSourcing

You'll need to create Commands, Queries, Events and so on. [Read the documentation](/documentation/cqrs.md).

### Event replaying

It you've added or changed a business rule, you may want to see how it would have behaved with the event stream,
this is a use case for event replaying.

You can replay event by aggregate id or by domain.

Let's say your a bank. The BI team said they want to send a SMS each time withdrawal is made if amount is
more than a value and they want to know how many SMS would have been sent.
You have to:
1. create the new event handler
2. re-play events

The event
```php
final class SendSMSOnWithdrawalPerformed implements EventHandler
{
    public function onWithdrawalPerformed(WithdrawalPerformed $event): void
    {
        $user = $this->userRepository->getOwner($event->getgetAccountId());
        if ($this->isMobilePhone($phoneNumber = $user->getPhoneNumber()) && $event->getAmount() > self::ALERT_AMOUNT) {
            // record the calls count somewhere, now you know how many SMS would have been sent
            $this->smsSender->send($phoneNumber);
        }
    }
}
```

Replay:
```bash
# by domain
bin/console botilka:event_store:replay --domain [domain name]
# or by id
bin/console botilka:event_store:replay --id [aggregate root id] # you can limit the scope with --from/-f & --to/-t
```

### Projection replay

In the same way than replaying events, you can replay projection. If you've added a projection
and you want to replay only this projection, use the `--matching/-m` options.

> Matching is a regex matched against `[ProjectorFQCN]::[method]`,
> ie. `App\BankAccount\Projection\Doctrine\BankAccountProjector::sumOfDeposit`

The projection
```php
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
            DepositPerformed::class => 'sumOfDeposit',
        ];
    }
}
```

Replay projection:
```bash
# by domain
bin/console botilka:projector:build domain [domain name]
# or by id
bin/console botilka:projector:build id [aggregate root id]  --matching sumOfDeposit # you can limit the scope with --from/-f & --to/-t
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
- Event upcasting.
- (maybe) Saga / Process manager.
- (maybe) Smart command retry on concurrency exception.


### Resources

- https://github.com/dddinphp/blog-cqrs
- https://github.com/broadway/broadway
- https://github.com/jorge07/symfony-4-es-cqrs-boilerplate (uses Broadway)
- https://github.com/CodelyTV/cqrs-ddd-php-example
- https://github.com/mnavarrocarter/ddd
- https://www.youtube.com/watch?v=qBLtZN3p3FU \[french\]
- https://www.youtube.com/watch?v=VpzSMz_XbqM \[french\]
