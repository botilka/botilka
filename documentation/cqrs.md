# CQRS



## Command

You need to create the command, its corresponding handler, the event and its event applier into the domain model.

The handler retrieve the domain model & call a method on it. The domain model method create
the domain event, apply it to itself and then return the event.

The event is 

Create a command:
```php
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

and it's handler:
```php
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
        return EventSourcedCommandResponse::fromEventSourcedAggregateRoot($instance, $event);
    }
}
```
Add the event applier into the domain model:
```php
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
use Botilka\Application\Command\CommandBus;

/** @var CommandBus $bus */
$bus = $container->get(CommandBus::class); // get it by injection

$command = new CreateBankAccountCommand('account in $', 'DOL');

$response = $bus->dispatch($command);

echo $response->getId(); // aggregate root id
```
