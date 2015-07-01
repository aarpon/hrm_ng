<?php

namespace hrm\User\Auth;

// Bootstrap
require_once dirname(__FILE__) . '/../../../bootstrap.php';

use hrm\User\UserQuery;

/**
 * Class AbstractAuthenticator Base Authenticator class that provides an interface
 *                             for concrete classes to implement.
 *
 * The User class expects concrete Authenticator classes to extend this class and
 * implement all of its abstract methods.
 *
 * @package hrm\User\Auth
 */
abstract class AbstractAuthenticator
{

    /**
     * Authenticates a User with given name and password.
     *
     * @param $username String Username for authentication.
     * @param $password String Password for authentication.
     * @return bool True if the authentication succeeded, false otherwise.
     */
    abstract public function authenticate($username, $password);

    /**
     * Returns the email address of User with given name.
     *
     * @param $username String Name of the User for which to query the email address.
     * @return String email address or "" if not found.
     */
    abstract public function getEmailAddress($username);

    /**
     * Return the group the User with given name belongs to.
     *
     * @param $username String Name of the User for which to query the group.
     * @return String Group or "" if not found.
     */
    abstract public function getGroup($username);

    /**
     * Checks whether the User with given name is active.
     *
     * @param $username String Name of the User for which to query the status.
     * @return bool True if the User is active, false otherwise.
     * @throws \Exception if the User is not found.
     */
    public function isActive($username)
    {
        // Get the User by name
        $user = UserQuery::create()->findOneByName($username);
        if (null === $user) {
            throw new \Exception("The requested user does not exist.");
        }

        // Test whether the status is 'active'
        return $user->getStatus() === "active";
    }

    /**
     * Checks whether the User with given name was suspended by ans
     * administrator.
     *
     * @param $username String Name of the User for which to query the status.
     * @return bool True if the User was suspended, false otherwise.
     * @throws \Exception if the User is not found.
     */
    public function isSuspended($username)
    {
        // Get the User by name
        $user = UserQuery::create()->findOneByName($username);
        if (null === $user) {
            throw new \Exception("The requested user does not exist.");
        }

        // Test whether the status is 'disabled'
        return $user->getStatus() === "disabled";
    }

    /**
     * Checks whether there is a request for a new User with given name.
     *
     * @param $username String Name of the User for which to query the status.
     * @return bool True if there is a request for the User, false otherwise.
     */
    public function isRequested($username)
    {
        // Get the User by name
        $user = UserQuery::create()->findOneByName($username);
        if (null === $user) {
            // There is no request for current User.
            return false;
        }

        // Test whether the status is 'requested'
        return $user->getStatus() === "requested";
    }

}
