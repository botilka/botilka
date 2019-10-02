<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDescriptionContainerPass;
use Botilka\Tests\Fixtures\Application\Command\ComplexCommand;
use Botilka\Tests\Fixtures\Application\Command\ParameterNotTypedCommand;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Application\Command\WithoutConstructorCommand;
use Botilka\Tests\Fixtures\Application\Query\ComplexQuery;
use Botilka\Tests\Fixtures\Application\Query\ParameterNotTypedQuery;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use Botilka\Tests\Fixtures\Application\Query\WithoutConstructorQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ApiPlatformDescriptionContainerPassTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    /** @var ApiPlatformDescriptionContainerPass */
    private $compilerPass;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new ApiPlatformDescriptionContainerPass();

        $this->container->setDefinition(DescriptionContainer::class, new Definition(DescriptionContainer::class));
    }

    public function testProcessCommand(): void
    {
        $container = $this->container;

        $container->setDefinition(SimpleCommand::class, (new Definition(SimpleCommand::class))->addTag('cqrs.command'));
        $container->setDefinition(ComplexCommand::class, (new Definition(ComplexCommand::class))->addTag('cqrs.command'));

        $this->compilerPass->process($container);

        $descriptionContainerDefinition = $container->getDefinition(Command::class.'.description_container');

        $expected = ['botilka_tests_fixtures_application_simple' => [
            'class' => 'Botilka\\Tests\\Fixtures\\Application\\Command\\SimpleCommand',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                ],
            ],
            'botilka_tests_fixtures_application_complex' => [
                'class' => 'Botilka\\Tests\\Fixtures\\Application\\Command\\ComplexCommand',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                    'biz' => [
                        'baz' => 'string',
                        'buz' => 'float',
                    ],
                    'lup' => 'string',
                    'ool' => '?string',
                ],
            ],
        ];

        self::assertSame($expected, $descriptionContainerDefinition->getArgument('$data'));
    }

    public function testProcessQuery(): void
    {
        $container = $this->container;

        $container->setDefinition(SimpleQuery::class, (new Definition(SimpleQuery::class))->addTag('cqrs.query'));
        $container->setDefinition(ComplexQuery::class, (new Definition(ComplexQuery::class))->addTag('cqrs.query'));

        $this->compilerPass->process($container);

        $descriptionContainerDefinition = $container->getDefinition(Query::class.'.description_container');

        $expected = ['botilka_tests_fixtures_application_simple' => [
            'class' => 'Botilka\Tests\Fixtures\Application\Query\SimpleQuery',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                ],
            ],
            'botilka_tests_fixtures_application_complex' => [
                'class' => 'Botilka\\Tests\\Fixtures\\Application\\Query\\ComplexQuery',
                'payload' => [
                    'foo' => 'string',
                    'bar' => '?int',
                    'biz' => [
                        'baz' => 'string',
                        'buz' => 'float',
                    ],
                    'lup' => 'string',
                    'ool' => '?string',
                ],
            ],
        ];

        self::assertSame($expected, $descriptionContainerDefinition->getArgument('$data'));
    }

    /** @dataProvider parameterNotTypedProvider */
    public function testParameterNotTyped(string $className, string $tagName): void
    {
        $container = $this->container;
        $container->setDefinition($className, (new Definition($className))->addTag($tagName));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Parameter 'bar' of class '{$className}' is not typed. Please type hint all Query & Command parameters.");

        $this->compilerPass->process($container);
    }

    public function parameterNotTypedProvider(): array
    {
        return [
            [ParameterNotTypedCommand::class, 'cqrs.command'],
            [ParameterNotTypedQuery::class, 'cqrs.query'],
        ];
    }

    /** @dataProvider processNoConstructorProvider */
    public function testProcessNoConstructor(string $className, string $tagName): void
    {
        $container = $this->container;
        $container->setDefinition($className, (new Definition($className))->addTag($tagName));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Class '{$className}' must have a constructor.");

        $this->compilerPass->process($container);
    }

    public function processNoConstructorProvider(): array
    {
        return [
            [WithoutConstructorCommand::class, 'cqrs.command'],
            [WithoutConstructorQuery::class, 'cqrs.query'],
        ];
    }

    public function testNoProcess(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(self::once())
            ->method('hasDefinition')
            ->with(DescriptionContainer::class)
            ->willReturn(false)
        ;

        $container->expects(self::never())
            ->method('getDefinition')
        ;

        $this->compilerPass->process($container);
    }
}
