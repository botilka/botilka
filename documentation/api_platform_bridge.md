# API Platform bridge

Botilka can detect [API Platform](https://api-platform.com/) and exposes commands & queries as resources.

## Usage

Each class implementing the [Command](/src/Command/Command.php) or [Query](/src/Query/Query.php) interface
is described on the API Platform UI using their constructor parameters.

Commands & Queries are tagged (thanks to Symfony DI component) and
a `CompilerPass` is used to inject the collection on API Platform DataProvider so a simple GET on the collection
shows you all existing possibilities ie.:

*Command*
```json
{
    "@id": "/api/cqrs/commands/app_bank_account_perform_deposit",
    "@type": "Command",
    "payload": {
        "accountId": "string",
        "amount": "int"
    }
}
```


*Query*
```json
{
    "@id": "/api/cqrs/queries/app_bank_account_find_bank_account_by_currency",
    "@type": "Query",
    "payload": {
        "currency": "string"
    }
}
```
