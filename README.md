# BOTILKA

[![Build Status](https://travis-ci.org/botilka/botilka.svg?branch=master)](https://travis-ci.org/botilka/botilka)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/botilka/botilka/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/botilka/botilka/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/botilka/botilka/?branch=master)

An modern & easy-to-use Event Sourcing & CQRS library framework. It's shipped with implementations built on top of Symfony components.

It can leverage [API Platform](https://api-platform.com) to expose the `Commands` and `Queries` via REST.

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
- EventStore in MongoDB / Redis.
- (maybe) Smart command retry on concurrency exception.

## Documentation

- [API Platform bridge](/documentation/api_platform_bridge.md)
- See below.

### How it works

Each `Command`, `Query` & `Event` are just POPO. For all of them, we use the Bus pattern to dispatch and
handle these messages. They are all transported on their own bus.

Buses are (by default) managed by [Symfony Messenger Component](https://symfony.com/doc/4.1/messenger.html).

Messages & handlers just have to implement an empty interface and everything is automatically wired
using auto-configuration.

The matching between a message and it(s) handler(s) is done by the Messenger component.
> The handler has an `__invoke` method with the type hinted message as the sole argument.
