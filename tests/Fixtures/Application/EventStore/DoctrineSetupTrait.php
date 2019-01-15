<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\EventStore;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

trait DoctrineSetupTrait
{
    private function setUpDatabase(KernelInterface $kernel): void
    {
        $application = new DropDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput(['--force' => true]), new NullOutput());

        $application = new CreateDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput([]), new NullOutput());
    }
}
