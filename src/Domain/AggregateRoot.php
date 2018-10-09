<?php

namespace Botilka\Domain;

/**
 * Domain model, includes the business logic.
 * MUST be isolated, MUST NOT reference any other AggregateRoot.
 * Take care of its own integrity.
 */
interface AggregateRoot
{
    public function getAggregateRootId(): string;
}
