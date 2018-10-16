<?php

namespace Botilka\Bus;


interface Bus
{
    public function dispatch($message);
}
