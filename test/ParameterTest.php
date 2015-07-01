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
    public function testCreateNAParameter()
    {
        // Instantiate the NumericalAperture class
        $NA = new \hrm\Param\NumericalAperture();
        $this->assertTrue($NA != null);

    }

}
