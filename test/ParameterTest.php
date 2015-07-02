<?php

/**
 * ParameterTest.php
 *
 * This test suite checks the all Parameter[Type]-related classes and database tables.
 */

// Bootstrap
require_once dirname(__FILE__) . '/../src/bootstrap.php';

class ParameterTest extends PHPUnit_Framework_TestCase
{
    public function testNAParameter()
    {
        // Instantiate the NumericalAperture class
        $NA = new \hrm\Param\NumericalAperture();
        $this->assertTrue($NA != null);

        // Get the name
        $name = $NA->getName();
        $this->assertTrue($name == "NumericalAperture");

        // Get the description
        $description = $NA->getDescription();
        $this->assertTrue($description == "Numerical aperture");

        // Set a valid NA value
        $this->assertTrue($NA->check(1.1));
        $NA->setValue(1.1);

        // Try saving the Parameter
        $this->assertTrue($NA->save() != 0); // Saving succeeded

        // Set an NA value out of range
        $this->assertFalse($NA->check(1.8));
        $NA->setValue(1.8);

        // Try saving the Parameter
        $this->assertTrue($NA->save() == 0); // Saving failed
    }

}
