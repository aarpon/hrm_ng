<?php

namespace hrm\Param\Core;

use hrm\Param\Core\Base\Parameter as BaseParameter;

/**
 * Class Parameter Base class for all Parameters
 * @package hrm\Param\Core
 */
class Parameter extends BaseParameter
{
    /**
     * Returns the Parameter type description.
     * @return string Description
     * @throws \Exception
     */
    public function getDescription()
    {
        $type = NumericalParameterTypeQuery::create()
                ->findOneByParameterTypeId($this->getParameterTypeId());
        if (null === $type) {
            throw new \Exception("Type not found.");
        }
        return $type->getDescription();
    }
}
