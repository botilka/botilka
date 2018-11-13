<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Projector\DefaultProjectorLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

final class ProjectorLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $projectors = $container->findTaggedServiceIds('botilka.projector');

        $locators = [];

        foreach ($projectors as $id => $tags) {
            $handledEvents = $this->guessHandledClasses(new \ReflectionClass($id), $id);

            foreach ($handledEvents as $event) {
                $locators[$event] = $locators[$event] ?? [] + [new Reference($id)];
            }
        }

        $locatorDefinition = $container->getDefinition(DefaultProjectorLocator::class);
        $locatorDefinition->setArgument('$data', $locators);
    }

    private function getProjectorReference(ContainerBuilder $container, array $handledEvents): array
    {
        $references = [];
        foreach ($handledEvents as $event) {
            $references[] = new Reference($event);
        }

        return $references;
    }

    private function guessHandledClasses(\ReflectionClass $handlerClass, string $serviceId): iterable
    {
        if ($handlerClass->implementsInterface(MessageSubscriberInterface::class)) {
            if (!$handledMessages = $handlerClass->getName()::getHandledMessages()) {
                throw new \RuntimeException(\sprintf('Invalid handler service "%s": method "%s::getHandledMessages()" must return one or more messages.', $serviceId, $handlerClass->getName()));
            }

            return $handledMessages;
        }

        try {
            $method = $handlerClass->getMethod('__invoke');
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(\sprintf('Invalid handler service "%s": class "%s" must have an "__invoke()" method.', $serviceId, $handlerClass->getName()));
        }

        $parameters = $method->getParameters();
        if (1 !== \count($parameters)) {
            throw new \RuntimeException(\sprintf('Invalid handler service "%s": method "%s::__invoke()" must have exactly one argument corresponding to the message it handles.', $serviceId, $handlerClass->getName()));
        }

        /** @var ?\ReflectionType $type */
        $type = $parameters[0]->getType();
        if (null === $type) {
            throw new \RuntimeException(\sprintf('Invalid handler service "%s": argument "$%s" of method "%s::__invoke()" must have a type-hint corresponding to the message class it handles.', $serviceId, $parameters[0]->getName(), $handlerClass->getName()));
        }

        if ($type->isBuiltin()) {
            throw new \RuntimeException(\sprintf('Invalid handler service "%s": type-hint of argument "$%s" in method "%s::__invoke()" must be a class , "%s" given.', $serviceId, $parameters[0]->getName(), $handlerClass->getName(), $type));
        }

        return [(string) $parameters[0]->getType()];
    }
}
