<?php

/**
 * UserTest.php
 *
 * This test suite checks the User class and database table.
 */

// Bootstrap
require_once dirname(__FILE__) . '/../src/bootstrap.php';

class UserTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var userID: the ID we will be using in the test,
     *              in case the database already contains
     *             data.
     */
    protected $userID;

    /**
     * Test instantiation of the \hrm\User class
     */
    public function testInstantiation()
    {
        $user = new \hrm\User();
        $this->assertTrue($user !== null);
    }


    /**
     * Test deleting all users from the database.
     *
     * This is not really a test. We just need the
     * User table to be empty at the beginning of the
     * test series.
     */
    public function testDeleteAllUsers()
    {
        # Create a UserQuery to retrieve a collection
        # of all User objects
        $users = \hrm\UserQuery::create()->find();

        # Look for the user we just added.
        foreach ($users as $user) {
            $user->delete();
        }
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
        $user->setPasswordHash("TestPassword");
        $user->setEmail("test@email.com");
        $user->setResearchGroup("TestGroup");
        $user->setAuthentication("active_dir");
        $user->setRole("user");
        $user->setCreationDate(new DateTime());
        $user->setLastAccessDate(new DateTime());
        $user->setStatus("active");

        # Save user
        $this->assertTrue($user->save() > 0);

        # Store its primary key and store it
        $this->userID = $user->getPrimaryKey();

        # Return the userID to be used in other tests
        return $this->userID;
    }

    /**
     * Test adding a user with duplicate name to the database.
     *
     * The name column is unique, therefore this must fail.
     *
     * @covers \hrm\User::save
     * @expectedException \Propel\Runtime\Exception\PropelException
     */
    public function testDuplicateUser()
    {
        $this->setExpectedException("\\Propel\\Runtime\\Exception\\PropelException");

        # Create user
        $user = new \hrm\User();

        # Set all properties (the name is the same)
        $user->setName("TestUser");
        $user->setPasswordHash("DifferentPassword");
        $user->setEmail("different@email.com");
        $user->setResearchGroup("differentGroup");
        $user->setAuthentication("ldap");
        $user->setRole("admin");
        $user->setCreationDate(new DateTime());
        $user->setLastAccessDate(new DateTime());
        $user->setStatus("disabled");

        # Save user: this will throw an expected exception
        # and will count as a test success.
        $user->save();
    }

    /**
     * Test retrieving the added user
     */
    public function testRetrieveUserByName()
    {
        # Create a UserQuery to retrieve the User by name.
        $user = \hrm\UserQuery::create()->findByName("TestUser");
        $this->assertTrue($user->count() == 1);
    }

    /**
     * Test retrieving the added user by UserQuery
     *
     * This needs to retrieve the userID we stored in testAddUser().
     *
     * @depends testAddUser
     */
    public function testRetrieveUserByPrimaryKey($userID)
    {
        # We need to store $userID again
        $this->userID = $userID;

        # Create a UserQuery
        $q = new \hrm\UserQuery();
        $user = $q->findPK($this->userID);
        $this->assertTrue($user !== null);

        # Check the name
        $this->assertTrue($user->getName() == "TestUser");
    }
}
