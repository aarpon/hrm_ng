<?php

namespace hrm\Param\Core;

use hrm\Param\Core\Base\NumericalParameter as BaseNumericalParameter;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * A NumericalParameter.
 */
class NumericalParameter extends BaseNumericalParameter
{

    /**
     * Implements the preSave() hook to perform validity checks on the value.
     *
     * If this function returns false, the object will not be saved in the database.
     * @param ConnectionInterface $conn Ignored.
     * @return bool True if the validation succeeded, false otherwise.
     */
    public function preSave(ConnectionInterface $conn = null)
    {
        // Return the resuklt of the value check
        return ($this->check($this->getValue()));
    }

    /**
     * Check whether the passed value can be set.
     *
     * @param float $value Value to be checked for validity.
     * @return bool True if the value can be set, false otherwise.
     * @throws \Exception If the NumericalParameterType could not be found.
     */
    public function check($value) {

        // Do we have something?
        if ($value === null)
        {
            return false;
        }

        // Get the parameter type
        $parameterType = NumericalParameterTypeQuery::create()
                ->findOneByName($this->getName());
        if (null === $parameterType) {
            throw new \Exception("Could not find the parameter type!");
        }

        // Perform the checks
        $minValue = $parameterType->getMinValue();
        if (null !== $minValue) {
            if ($value < $minValue) {
                return false;
            }
        }
        $maxValue = $parameterType->getMaxValue();
        if (null !== $maxValue) {
            if ($value > $maxValue) {
                return false;
            }
        }

        return true;
    }

}
