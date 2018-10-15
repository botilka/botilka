<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    private $tmpRootDir = '/tmp';

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        $this->tmpRootDir = \exec('mktemp -d'); // retrieve a new temporary root dir on each execution
    }

    public function getCacheDir()
    {
        return $this->tmpRootDir.'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->tmpRootDir.'/logs/'.$this->environment;
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config.yaml');
    }

    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
//            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
//            new \ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle(),
            new \Botilka\Bridge\Symfony\Bundle\BotilkaBundle(),
        ];
    }
}
