# How it works

## Command, query & event handling

`Command`, `Query` & `Event` and their handlers are **empty** interfaces. Let's see how it works.

Each `Command`, `Query` & `Event` are just POPO seen as messages. For all of them, we use the bus pattern to dispatch and
handle these messages. They are all transported on their own bus.

There is a default implementation of these buses with the [Symfony Messenger Component](https://symfony.com/doc/4.1/messenger.html).

Messages & handlers are implementing their respective interfaces and first magic happend here: Botilka tags all the handler with `messenger.message_handler`,
everything is automatically wired using auto-configuration and the matching between a message and it(s) handler(s) is done by the Messenger component.

> Botilka recommands to have the `__invoke` method with the type hinted message as the sole argument,
> but you can use all the features provided by the Messenger component. 

## Event dispatching

A command generate an event. This event has 2 differents handling: domain event handlers & projections.

### Event handler

Distpatching an event to it's hanlder is done by a [bus middleware](src/Botilka/Infrastructure/Symfony/Messenger/Middleware/EventDispatcherMiddleware.php)
that dispatch this event on the event bus.


### Projector

Even if projector is just another event handler, event handlers & projections have a real different meaning in ES/CQRS, so Botilka
have choosed to separate them. By doing that, it enable the capability to work with projectors in a datached way from events and allows us to
re-execute projector from the event stream without having event triggered, so no business-side effect (no mails resent, ...).
So if you decide to create a new projection, you can build it from the event store in production! 

> If your projectors are slow, make them async!

The same middleware used for event handlers is used too to call the projectors. 
