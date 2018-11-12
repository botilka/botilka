<?php

namespace Botilka\Tests;

use Botilka\Tests\app\AppKernel;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    protected static $class = AppKernel::class;

    public static function bootKernel(array $options = [])
    {
        return parent::bootKernel($options + ['environment' => 'test']);
    }

    public static function setUpDoctrine(KernelInterface $kernel): void
    {
        $application = new DropDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput(['--force' => true]), new NullOutput());

        $application = new CreateDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput([]), new NullOutput());
    }
}
