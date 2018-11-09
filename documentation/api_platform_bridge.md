# API Platform bridge

Botilka can detect [API Platform](https://api-platform.com/) and exposes commands & queries as resources.

## Usage

### Execution

TODO

### Description

Each class implementing the [Command](/src/Application/Command/Command.php) or [Query](/src/Application/Query/Query.php) interface
is described on the API Platform UI using their constructor parameters.

A description consist of the name of the *item* and what's expected as payload.

You can get the description by issuing a GET request on `/cqrs/description/commands` / `/cqrs/description/queries`. 

*Command*
```json
{
    "@id": "/api/cqrs/description/commands/app_bank_account_perform_deposit",
    "@type": "Command",
    "payload": {
        "accountId": "string",
        "amount": "float",
        "reason": "?string"
    }
}
```

*Query*
```json
{
    "@id": "/api/cqrs/description/queries/app_bank_account_find_bank_account_by_currency",
    "@type": "Query",
    "payload": {
        "currency": "string"
    }
}
```

## How it works

Botilka add a [`Command`](/src/Bridge/ApiPlatform/Resource/Command.php) & a [`Query`](/src/Bridge/ApiPlatform/Resource/Query.php) resource to API Platform that describe the commands & queries from the domain.
These descriptions are registered in a [`DescriptionContainer`](/src/Bridge/ApiPlatform/Description/DescriptionContainer.php).

### Handling

#### Commands

For each command, the [`BotilkaCommandResourceMetadataFactory`](/src/Bridge/ApiPlatform/Metadata/Resource/Factory/BotilkaCommandResourceMetadataFactory.php) add a custom POST collection operation on the `Command` resource
with [`CommandHandlerAction`](/src/Bridge/ApiPlatform/Action/CommandHandlerAction.php) as controller.

When issuing a POST request on this operation, [`CommandResourceClassEventListener`](/src/Bridge/ApiPlatform/EventListener/CommandResourceClassEventListener.php) change the HTTP query attribute
`_api_resource_class` to the domain command class to be handled. API Platform will deserialize, validate
and pass it to the `CommandHandlerAction` that will dispatch it and return the result.

#### Queries

For each query, the [`BotilkaQueryResourceMetadataFactory`](/src/Bridge/ApiPlatform/Metadata/Resource/Factory/BotilkaQueryResourceMetadataFactory.php) add a custom GET item operation on the `Query` resource
with `CommandHandlerAction` as controller.

When issuing a GET request on this operation, [`QueryResourceClassEventListener`](/src/Bridge/ApiPlatform/EventListener/QueryResourceClassEventListener.php) catch the request, create
& dispatch the corresponding domain query. It then sets the HTTP query attribute `_api_receive` to `false` to by-pass the `ReadListener` and
put the result in the `data` attribute so API Platform will handle the rest of the request.

### Description

When requesting a description, the corresponding `DataProvider` return the data stored in `DescriptionContainer`.
