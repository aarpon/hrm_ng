<?php

namespace hrm\Auth;

// Bootstrap
require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Class AuthenticatorFactory Return the correct Authenticator class depending
 * on the passed string.
 *
 * @package hrm\Auth
 */
class AuthenticatorFactory
{

    /**
     * Returns the correct authenticator object depending on the passed string.
     *
     * @param $auth String The requested authentication mechanism. One of:
     *              - integrated
     *              - active_dir
     *              - ldap
     * @return AbstractAuthenticator The requested authenticator object (one of the
     *                               concrete classes).
     * @throws \Exception if the authentication mode is not recognized.
     */
    public static function getAuthenticator($auth)
    {

        // Initialize and return the authenticator
        switch ($auth) {

            case "integrated":

                return new IntegratedAuthenticator();

            case "LDAP":

                throw new \Exception("Not implemented yet!");

            case "ACTIVE_DIR":

                throw new \Exception("Not implemented yet!");

            default:

                throw new \Exception("Unrecognized authentication mechanism!");
        }

    }
}
