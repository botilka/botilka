<?php

namespace Botilka\Tests;

use Botilka\Tests\app\AppKernel;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    protected static $class = AppKernel::class;

    public static function bootKernel(array $options = [])
    {
        return parent::bootKernel($options + ['environment' => 'test']);
    }

    public static function setUpDoctrine(KernelInterface $kernel)
    {
        $application = new DropDatabaseDoctrineCommand();
        $application->setContainer(self::$container);

        $input = new ArrayInput([
            '--force' => true,
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $application = new CreateDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $output = new NullOutput();
        $input = new ArrayInput([]);
        \ob_start();
        $application->run($input, $output);
        $stdout = \ob_get_contents();
        \ob_end_flush();
    }
}
