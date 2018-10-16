<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDescriptionContainerPass;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Application\Command\WithoutConstructorCommand;
use Botilka\Tests\Fixtures\Application\Command\WithValueObjectCommand;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use Botilka\Tests\Fixtures\Application\Query\WithoutConstructorQuery;
use Botilka\Tests\Fixtures\Application\Query\WithValueObjectQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ApiPlatformDescriptionContainerPassTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    /** @var ApiPlatformDescriptionContainerPass */
    private $compilerPass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new ApiPlatformDescriptionContainerPass();

        $this->container->setDefinition(DescriptionContainer::class, new Definition(DescriptionContainer::class));
    }

    public function testProcessCommand()
    {
        $container = $this->container;

        $container->setDefinition(SimpleCommand::class, (new Definition(SimpleCommand::class))->addTag('cqrs.command'));
        $container->setDefinition(WithValueObjectCommand::class, (new Definition(WithValueObjectCommand::class))->addTag('cqrs.command'));

        $this->compilerPass->process($container);

        $descriptionContainerDefinition = $container->getDefinition(Command::class.'.description_container');

        $expected = ['botilka_tests_fixtures_application_simple' => [
            'class' => 'Botilka\\Tests\\Fixtures\\Application\\Command\\SimpleCommand',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                ],
            ],
            'botilka_tests_fixtures_application_with_value_object' => [
                'class' => 'Botilka\\Tests\\Fixtures\\Application\\Command\\WithValueObjectCommand',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                    'biz' => [
                        'baz' => 'string',
                        'buz' => 'float',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $descriptionContainerDefinition->getArgument('$data'));
    }

    public function testProcessQuery()
    {
        $container = $this->container;

        $container->setDefinition(SimpleQuery::class, (new Definition(SimpleQuery::class))->addTag('cqrs.query'));
        $container->setDefinition(WithValueObjectQuery::class, (new Definition(WithValueObjectQuery::class))->addTag('cqrs.query'));

        $this->compilerPass->process($container);

        $descriptionContainerDefinition = $container->getDefinition(Query::class.'.description_container');

        $expected = ['botilka_tests_fixtures_application_simple' => [
            'class' => 'Botilka\Tests\Fixtures\Application\Query\SimpleQuery',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                ],
            ],
            'botilka_tests_fixtures_application_with_value_object' => [
                'class' => 'Botilka\\Tests\\Fixtures\\Application\\Query\\WithValueObjectQuery',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                    'biz' => [
                        'baz' => 'string',
                        'buz' => 'float',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $descriptionContainerDefinition->getArgument('$data'));
    }

    /** @dataProvider processNoConstructorProvider */
    public function testProcessNoConstructor(string $className, string $tagName)
    {
        $container = $this->container;
        $container->setDefinition($className, (new Definition($className))->addTag($tagName));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Class \"$className\" must have a constructor.");

        $this->compilerPass->process($container);
    }

    public function processNoConstructorProvider(): array
    {
        return [
            [WithoutConstructorCommand::class, 'cqrs.command'],
            [WithoutConstructorQuery::class, 'cqrs.query'],
        ];
    }
}
