<?php

namespace hrm\auth;

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
     * @param $password String Password for authentication.
     * @return bool True if the authentication succeeded, false otherwise.
     * @throws \Exception if password hashing failed.
     */
    public function authenticate($username, $password)
    {
        // Retrieve the User from the database by name
        $user = UserQuery::create()->findByName($username);
        if ($user->count() == 0) {
            return false;
        }

        // Retrieve the password from the database
        $passwordHash = $user["password_hash"];
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
            // TODO: Check that this is the correct way to store the new password
            $user["password_hash"] = password_hash(
                $password,
                    $currentHashAlgorithm,
                    $currentHashOptions);

            // TODO: Check that the user is complete.
            $user->save();
        }
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
        $user = UserQuery::create()->findByName($username);

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
        $user = UserQuery::create()->findByName($username);

        // Return the group
        return $user["research_group"];
    }
}