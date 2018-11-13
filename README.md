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

Botilka provide a command to create the event store:

```sh
bin/console botilka:event_store:initialize doctrine # or 'mongodb'
```
you can force recreate:
```sh
bin/console botilka:event_store:initialize doctrine -f # or 'mongodb'
```

## Usage

### CQRS & EventSourcing

You'll need to create Commands, Queries, and so on. [Read the documentation](/documentation/cqrs.md).


### API Platform bridge
See the [API Platform bridge](/documentation/api_platform_bridge.md) documentation.

### How it works

Each `Command`, `Query` & `Event` are just POPO seen as messages. For all of them, we use the Bus pattern to dispatch and
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
