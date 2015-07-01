<?php

namespace hrm\Param;

/**
 * The NumericalAperture Parameter.
 */
class NumericalAperture extends Core\NumericalParameter
{
    /**
     * Constructor: must call the base constructor.
     *
     * @throws \Exception if the base constructor failed.
     */
    public function __construct()
    {
        parent::__construct("NumericalAperture");

    }
}
