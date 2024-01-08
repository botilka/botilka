<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BotilkaBundle extends Bundle
{
    public function build(ContainerBuilder $container): void {}
}
