<?php

namespace hrm\Param;

/**
 * The NumberOfIterations Parameter.
 */
class NumberOfIterations extends Core\NumericalParameter
{
    use ParameterTrait;

    /**
     * Constructor: must call the ParameterTrait::init() method.
     */
    public function __construct()
    {
        // Map to its type
        ParameterTrait::init($this);
    }
}
