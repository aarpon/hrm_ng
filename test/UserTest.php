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

    /**
     * Test adding a user to the database.
     */
    public function testAddUser()
    {
        # Create user
        $user = new \hrm\User();

        # Set all properties
        $user->setName("TestUser");
        $user->setPassword("TestPassword");
        $user->setEmail("test@email.com");
        $user->setResearchGroup("TestGroup");
        $user->setRole("user");
        $user->setCreationDate(new DateTime());
        $user->setLastAccessDate(new DateTime());
        $user->setStatus("active");

        # Save user
        $this->assertTrue($user->save() > 0);

    }
}
