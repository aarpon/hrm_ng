<?php

namespace hrm;

use hrm\Base\User as BaseUser;
use hrm\Auth\AuthenticatorFactory;

/**
 * User class.
 *
 */
class User extends BaseUser
{

    /**
     * @var AbstractAuthenticator The authenticator that will check for the User credentials.
     *
     * Depending on the configuration in the database, the actual authenticator
     * will be one of the classes extending AbstractAuthenticator.
     */
    protected $authenticator = null;

    /**
     * @var bool Flag to indicate whether the user is currently logged in.
     */
    protected $isLoggedIn = false;

    /**
     * Return the login status of the User.
     *
     * @return bool True if the user is logged in, false otherwise.
     */
    public function isLoggedIn()
    {
        // Return the isLoggedIn flag
        return $this->isLoggedIn;
    }

    /**
     * Attempts to login the User.
     * @param $password String Password to be checked for current User.
     * @return bool True if the login succeeded, false otherwise.
     * @throws \Exception if the User name is not set when the
     *                    logIn() method is called.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function logIn($password)
    {
        // If user name and password have not been set yet, throw an \Exception
        if ($this->getName() === "") {
            throw new \Exception("User name must be set " .
            "before the User::logIn() method can be called.");
        }

        // Get the authenticator
        $auth = $this->getAuthentication();
        // TODO: Get the authenticator that matches the authentication mechanism
        // TODO: specified in the database for the specific User. Currently, the
        // TODO: Authenticator is IntegratedAuthenticator for everyone.
        // TEMP!
        $auth = "integrated";
        $this->authenticator = AuthenticatorFactory::getAuthenticator($auth);

        // Authenticate the User against the selected mechanism
        $this->isLoggedIn = $this->authenticator->authenticate(
                $this->getName(), $password);

        // Update the last access date for a successful login
        if ($this->isLoggedIn == true) {
            $this->setLastAccessDate(new \DateTime());

            // Store the change in the database
            $this->save();
        }

        // Return current login status
        return $this->isLoggedIn;
    }

    /**
     * Hashes the password before it is ready to be stored in the database.
     *
     * This function uses the Password Hashing API from PHP >= 5.5 to hash the
     * password before it calls the parent SetPasswordHash method with the hashed
     * password.
     *
     * @param string $password Password to be stored.
     * @return void
     * @throws \Exception If password hashing failed.
     */
    public function SetPasswordHash($password)
    {
        // Create password hash
        $passwordHash = password_hash(
                $password, PASSWORD_DEFAULT, ['cost' => 15]);

        /// Check that the hashing worked
        if ($passwordHash === false) {
            throw new \Exception('Password hash failed');
        }

        // Now call the base method
        parent::setPasswordHash($passwordHash);
    }
}