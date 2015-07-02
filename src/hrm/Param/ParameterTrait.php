<?php

namespace hrm\Param;

trait ParameterTrait {

    /**
     * Sets up the calling object
     * @param $class \hrm\Param\Core\Parameter The calling object should pass '$this'
     * @return void
     * @throws \Exception
     */
    public static function init($class)
    {
        // Get the parameter name
        $c = new \ReflectionClass(get_class($class));
        $parameterName = $c->getShortName();

        // Set the parameter name (dynamically)
        $class->setName($parameterName);

        // Get the correct query for the Parameter
        // TODO: Make sure that the parent class is one of the correct types!
        $baseClass = get_parent_class($class);
        $queryObject = call_user_func($baseClass . "TypeQuery::create");

        // Retrieve the corresponding type and its id
        $parameterType = $queryObject->findOneByName($parameterName);
        if (null === $parameterType) {
            throw new \Exception("Could not find the $parameterName type!");
        }
        $parameterTypeId = $parameterType->getId();

        // Set the parameter type id
        // TODO: Should we store the type object instead of an id? This way we spare
        // TODO: retrieving the object from the DB every time.
        $class->setParameterTypeId($parameterTypeId);

    }

}