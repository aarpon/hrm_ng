<?php

namespace hrm\Auth;

// Bootstrap
require_once dirname(__FILE__) . '/../../bootstrap.php';

use \hrm\UserQuery;

class IntegratedAuthenticator extends AbstractAuthenticator {

    /**
     * Authenticates a User with given name and password.
     *
     * This uses the Password Hashing API available in PHP >= 5.5.
     *
     * @param $username String User name for authentication.
     * @param $password String Password for authentication. This will be hashed
     *                         and compared to the one stored.
     * @return bool True if the authentication succeeded, false otherwise.
     * @throws \Exception if password hashing failed.
     */
    public function authenticate($username, $password)
    {
        // Retrieve the User from the database by name
        $users = UserQuery::create()->findByName($username);
        if ($users->count() == 0) {
            return false;
        }

        // Get the User from the collection
        $user = $users[0];

        // Retrieve the password from the database
        $passwordHash = $user->getPasswordHash();
        if ($passwordHash == "") {
            // If the password hash is empty or null, return false.
            return false;
        }

        // Check the password
        if (password_verify($password, $passwordHash) === false) {
            return false;
        }

        // Re-hash password if necessary
        $currentHashAlgorithm = PASSWORD_DEFAULT;
        $currentHashOptions = array('cost' => 15);
        $passwordNeedsRehash = password_needs_rehash(
            $passwordHash,
                $currentHashAlgorithm,
                $currentHashOptions);
        if ($passwordNeedsRehash === true) {
            // Update the password hash
            $user->setPasswordHash(password_hash(
                $password,
                    $currentHashAlgorithm,
                    $currentHashOptions));

            // Update the User in the database
            $user->save();
        }

        // Return successful login
        return true;
    }

    /**
     * Returns the email address of user with given username.
     *
     * @param $username String Username for which to query the email address.
     * @return String email address or NULL
     */
    public function getEmailAddress($username)
    {
        // Retrieve the User from the database by name
        $user = UserQuery::create()->findOneByName($username);
        if (null === $user) {
            return "";
        }

        // Return the e-mail address
        return $user["email"];
    }

    /**
     * Return the group the user with given username belongs to.
     * @param $username String Username for which to query the group.
     * @return String Group or "" if not found.
     */
    public function getGroup($username)
    {
        // Retrieve the User from the database by name
        $user = UserQuery::create()->findOneByName($username);
        if (null === $user) {
            return "";
        }

        // Return the group
        return $user["research_group"];
    }
}