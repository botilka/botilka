<?php

declare(strict_types=1);

namespace Botilka\Tests\app;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    private $volatileDir = '/tmp/botilka_test';

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        $this->volatileDir = sys_get_temp_dir().'/botilka_test';
    }

    public function getCacheDir()
    {
        return $this->volatileDir.'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->volatileDir.'/logs/'.$this->environment;
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->setParameter('kernel.project_dir', __DIR__);
        $loader->load($this->getRootDir().'/config/config.yaml');
        $loader->load("{$this->getRootDir()}/config/services_test.yaml");
    }

    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
//            new \ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle(),
            new \Botilka\Bridge\Symfony\Bundle\BotilkaBundle(),
        ];
    }
}
