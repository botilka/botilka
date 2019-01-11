<?php

declare(strict_types=1);

namespace Botilka\Tests;

use Botilka\Tests\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    protected static $class = AppKernel::class;

    public static function bootKernel(array $options = [])
    {
        return parent::bootKernel($options + ['environment' => 'test']);
    }
}
