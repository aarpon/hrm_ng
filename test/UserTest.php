<?php

// Bootstrap
require_once dirname(__FILE__) . '/../src/bootstrap.php';

class UserTest extends PHPUnit_Framework_TestCase {

    /**
     * Test instantiation of the \hrm\User class
     */
    public function testInstantiation()
    {
        $user = new \hrm\User();
        $this->assertTrue($user !== null);
    }

}
