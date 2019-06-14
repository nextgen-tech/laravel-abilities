<?php

namespace NGT\Laravel\Abilities\Exceptions;

use Exception;

class GateNotExists extends Exception
{
    public function __construct($gate)
    {
        parent::__construct(sprintf('Gate "%s" not exists.', $gate));
    }
}
