<?php

namespace Botilka\Tests\Ui\Console;

use Botilka\Event\EventReplayerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BotilkaReplayCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->markTestSkipped('Not yet implemented');
//        $kernel = static::bootKernel();
//        $application = new Application($kernel);
//
//        $container = self::$container;
//        $eventReplayer = $this->createMock(EventReplayerInterface::class);
//        $container->set(EventReplayerInterface::class, $eventReplayer);
//
//        $command = $application->find('botilka:replay');
//        $commandTester = new CommandTester($command);
//        $commandTester->execute([
//            'command' => $command->getName(),
//            'id' => 'foo',
//        ]);
    }
}
