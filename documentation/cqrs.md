# CQRS



### Commands & Queries

#### Handling

Handlers get their dependencies (collaborators) using constructor injection:
- `CommandHandler` need an Repository (event sourced or not)
- `QueryHandler` & `EventHandler` use whatever collaborator you want

*Sample command & handler*
```php
// src/TheDomain/Application/Command/TheCommand.php
namespace App\TheDomain\Application\Command;

use Botilka\Application\Command\Command;

final class TheCommand implements Command {
    
    private $modelId;
    private $what;
    
    public function __construct(string $modelId, string $what)
    {
        $this->modelId = $modelId;
        $this->what = $what;
    }
    
    // add getters
}

// src/TheDomain/Application/Command/TheCommandHandler.php
namespace App\TheDomain\Application\Command;

use Botilka\Application\Command\CommandHandler;
use App\TheDomain\Domain\TheDomainModel;

final class TheCommandHandler implements CommandHandler {

    private $repository;
    
    public function __construct(TheEventSourcedRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(TheCommand $command): CommandResponse
    {
        $theDomainModel = $this->repository->get($command->getModelId());
        /** @var TheDomainModel $instance */
        [$instance, $event] = $theDomainModel->makeSomething($command->getWhat(), $command->getWhy());

        return CommandResponse::withValue($instance->getAggregateRootId(), $instance->getPlayhead(), $event);
    }
}
```
