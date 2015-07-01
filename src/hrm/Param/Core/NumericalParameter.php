<?php

namespace hrm\Param\Core;

use hrm\Param\Core\Base\NumericalParameter as BaseNumericalParameter;

/**
 * A NumericalParameter.
 */
class NumericalParameter extends BaseNumericalParameter
{
    /**
     * @var NumericalParameterType The associated ParameterType
     */
    protected $parameterType;

    /**
     * Constructor
     * @param $parameterName string Name of the Parameter.
     * @throws \Exception If the associated Parameter type could not be found.
     */
    public function __construct($parameterName) {

        // Get and associate the NumericalAperture type
        $type = NumericalParameterTypeQuery::create()->findOneByName($parameterName);
        if (null === $type) {
            throw new \Exception("Could not find the $parameterName type!");
        }

        // Store
        $this->parameterType = $type;
    }
}
