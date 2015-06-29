<?php

namespace hrm;

use hrm\Base\User as BaseUser;

/**
 * User class.
 *
 */
class User extends BaseUser
{
    /**
     * @var bool Flag to indicate whether the user is currently logged in.
     */
    protected $isLoggedIn = false;

    /**
     * Return the login status of the User.
     *
     * @return bool True if the user is logged in, false otherwise.
     */
    public function isLoggedId()
    {
        return $this->isLoggedIn;
    }

    /**
     * Attempts to login the User.
     *
     * @return bool True if the login succeeded, false otherwise.
     */
    public function login()
    {
        // TODO: Implement
        $this->isLoggedIn = true;

        // Return login status
        return $this->isLoggedIn;
    }

}
