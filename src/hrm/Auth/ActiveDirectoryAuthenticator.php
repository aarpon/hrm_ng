<?php

namespace hrm\Auth;

// Bootstrap
use adLDAP\adLDAP;

require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Class ActiveDirectoryAuthenticator
 *
 * Manages Active Directory connections through the adLDAP library.
 *
 * The configuration file for the ActiveDirectoryAuthenticator class is
 * config/auth/active_directory_config.inc. A sample configuration file is
 * config/auth/active_directory_config.inc.sample.
 * A user with read-access to Active Directory must be set up in the
 * configuration file for queries to be possible.

 * @package hrm\Auth
 */
class ActiveDirectoryAuthenticator extends AbstractAuthenticator {

    /**
     * @var $m_AdLDAP AdLDAP The AdLDAP connection object
     */
    private $m_AdLDAP;

    /**
     * @var $m_GroupIndex Integer Index (level) of the group to consider
     *
     * Users usually belong to several groups, m_GroupIndex defines which
     * level of the hierarchy to consider. If $m_GroupIndex is -1 and the
     * $m_ValidGroups array is empty, ActiveDirectoryAuthenticator::getGroup()
     * will return an array with all groups.
     */
    private $m_GroupIndex;

    /**
     * @var $m_ValidGroups  Array Array of valid groups
     *
     * If $m_GroupIndex is set to -1 and $m_ValidGroups is not empty,
     * the groups array returned by adLDAP->user_groups will be compared
     * with $m_ValidGroups and only the first group in the intersection
     * will be returned (ideally, the intersection should contain only
     * one group).
     */
    private $m_ValidGroups;

    /**
     * @var $m_UsernameSuffix TODO: Complete
     */
    private $m_UsernameSuffix;

    /**
     * @var $m_UsernameSuffixReplaceMatch TODO: Complete
     */
    private $m_UsernameSuffixReplaceMatch;

    /**
     * @var $m_UsernameSuffixReplaceString TODO: Complete
     */
    private $m_UsernameSuffixReplaceString;

    /**
     * Constructor: instantiates an ActiveDirectoryAuthenticator object
     * with the settings specified in the configuration file. No parameters
     * are passed to the constructor.
     */
    public function __construct() {

        global $ACTIVE_DIR_CONF;

        // Include configuration file
        include(dirname(__FILE__) . "/../../../config/auth/active_directory_config.inc");

        // Set up the adLDAP object
        $options = array(
                'account_suffix'     => $ACTIVE_DIR_CONF['account_suffix'],
                'ad_port'            => $ACTIVE_DIR_CONF['ad_port'],
                'base_dn'            => $ACTIVE_DIR_CONF['base_dn'],
                'domain_controllers' => $ACTIVE_DIR_CONF['domain_controllers'],
                'admin_username'     => $ACTIVE_DIR_CONF['ad_username'],
                'admin_password'     => $ACTIVE_DIR_CONF['ad_password'],
                'real_primarygroup'  => $ACTIVE_DIR_CONF['real_primary_group'],
                'use_ssl'            => $ACTIVE_DIR_CONF['use_ssl'],
                'use_tls'            => $ACTIVE_DIR_CONF['use_tls'],
                'recursive_groups'   => $ACTIVE_DIR_CONF['recursive_groups']);

        $this->m_GroupIndex      =  $ACTIVE_DIR_CONF['group_index'];
        $this->m_ValidGroups     =  $ACTIVE_DIR_CONF['valid_groups'];

        $this->m_UsernameSuffix = $ACTIVE_DIR_CONF['ad_username_suffix'];
        $this->m_UsernameSuffixReplaceMatch =
                $ACTIVE_DIR_CONF['ad_username_suffix_pattern'];
        $this->m_UsernameSuffixReplaceString =
                $ACTIVE_DIR_CONF['ad_username_suffix_replace'];

        try {
            $this->m_AdLDAP = new adLDAP($options);
        } catch (adLDAPException $e) {
            // Make sure to clean stack traces
            $pos = stripos($e, 'AD said:');
            if ($pos !== false) {
                $e = substr($e, 0, $pos);
            }
            echo $e;
            exit();
        }
    }

    /**
     * Destructor. Closes the connection started by the adLDAP object.
     */
    public function __destruct() {
        // We ask the adLDAP object to close the connection. A check whether a
        // connection actually exists will be made by the adLDAP object itself.
        // This is a fallback to make sure to close any open sockets when the
        // object is deleted, since all methods of this class that access the
        // adLDAP object explicitly close the connection when done.
        $this->m_AdLDAP->close();
    }

    /**
     * Authenticates the User with given username and password against
     * Active Directory.
     *
     * @param String $username Username for authentication.
     * @param String $password Password for authentication.
     * @return bool True if authentication succeeded, false otherwise.
     * @throws \Exception
     */
    public function authenticate($username, $password) {

        // Make sure the user is active
        if (!$this->isActive($username)) {
            return false;
        }
        // Authenticate against AD
        $b = $this->m_AdLDAP->user()->authenticate(
                strtolower($username), $password);
        $this->m_AdLDAP->close();
        return $b;
    }

    /**
     * Return the email address of user with given username.
     *
     * @param String $username Username for which to query the email address.
     * @return string Email address or NULL
     */
    public function getEmailAddress($username) {

        // Logger
        global $HRM_LOGGER;

        // If needed, process the user name suffix for subdomains
        $username .= $this->m_UsernameSuffix;
        if ($this->m_UsernameSuffixReplaceMatch != '') {
            $pattern = "/$this->m_UsernameSuffixReplaceMatch/";
            $username = preg_replace($pattern,
                    $this->m_UsernameSuffixReplaceString,
                    $username);
        }

        // Get the email from AD
        $info = $this->m_AdLDAP->user()->infoCollection(
                $username, array("mail"));

        $this->m_AdLDAP->close();
        if (!$info) {
            $HRM_LOGGER->warning('No email address found for username "' .
                    $username . '"');
            return "";
        }
        $HRM_LOGGER->info('Email for username "' . $username . '": ' . $info->mail);
        return $info->mail;
    }

    /**
     * Return the group the user with given username belongs to.
     *
     * @param String $username Username for which to query the group.
     * @return string Group or "" if not found.
     */
    public function getGroup($username) {

        global $HRM_LOGGER;

        // If needed, process the user name suffix for subdomains
        $username .= $this->m_UsernameSuffix;
        if ($this->m_UsernameSuffixReplaceMatch != '') {
            $pattern = "/$this->m_UsernameSuffixReplaceMatch/";
            $username = preg_replace($pattern,
                    $this->m_UsernameSuffixReplaceString,
                    $username);
        }

        // Get the user groups from AD
        $userGroups = $this->m_AdLDAP->user()->groups($username);
        $this->m_AdLDAP->close();

        // If no groups found, return ""
        if (!$userGroups) {
            $HRM_LOGGER->warning('No groups found for username "' . $username . '"');
            return "";
        }

        // Make sure to work on an array
        if (!is_array($userGroups)) {
            $userGroups = array($userGroups);
        }

        // If the list of valid groups is not empty, find the intersection
        // with the returned group list; otherwise, keep working with the
        // original array.
        if (count($this->m_ValidGroups) > 0) {
            $userGroups = array_values(array_intersect(
                    $userGroups, $this->m_ValidGroups));
        }

        // If an explicit index is set in the configuration file, return the
        // group at that position; otherwise, just return the first entry in
        // the (filtered or original) group array.
        if ($this->m_GroupIndex >= 0 &&
                $this->m_GroupIndex < count($userGroups)) {
            $group = $userGroups[$this->m_GroupIndex];
        } else {
            $group = $userGroups[0];
        }
        $HRM_LOGGER->warning('Group for username "' . $username . '": ' . $group);
        return $group;

    }

}
