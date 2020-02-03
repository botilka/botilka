<?php

declare(strict_types=1);

namespace Botilka\Tests;

use Botilka\Tests\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    /** @var string */
    protected static $class = AppKernel::class;

    public static function bootKernel(array $options = []): KernelInterface
    {
        return parent::bootKernel($options + ['environment' => 'test']);
    }
}
