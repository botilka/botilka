<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class HydrationException extends \Exception
{
    private $constraintViolationList;

    public function __construct(ConstraintViolationListInterface $constraintViolationList, string $message = '', int $code = 0, \Exception $previous = null)
    {
        $this->constraintViolationList = $constraintViolationList;

        parent::__construct($message, $code, $previous);
    }

    public function getConstraintViolationList()
    {
        return $this->constraintViolationList;
    }
}
