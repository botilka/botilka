<?php

declare(strict_types=1);

namespace Botilka\Tests\app;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    private string $volatileDir = '/tmp/botilka_test';

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        $this->volatileDir = sys_get_temp_dir().'/botilka_test';
    }

    public function getCacheDir(): string
    {
        return $this->volatileDir.'/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->volatileDir.'/logs/'.$this->environment;
    }

    /**
     * @return Bundle[]
     */
    public function registerBundles(): array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            //            new \ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle(),
            new \Botilka\Bridge\Symfony\Bundle\BotilkaBundle(),
        ];
    }
}
