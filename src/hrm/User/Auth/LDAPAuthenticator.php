<?php

namespace hrm\User\Auth;

// Bootstrap
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 * Class LDAPAuthenticator
 *
 * Manages LDAP connections through built-in PHP LDAP support
 *
 * The configuration file for the LDAPAuthenticator class is
 * config/auth/ldap_config.inc.
 * A sample configuration file is config/auth/ldap_config.inc.sample.
 * A user with read-access to the LDAP server must be set up in the
 * configuration file for queries to be possible.
 *
 * @package hrm\User\Auth
 */
class LDAPAuthenticator extends AbstractAuthenticator {

    /**
     * @var resource $m_Connection An LDAP resource
     */
    private $m_Connection;

    /**
     * @var string $m_Host LDAP host name
     */
    private $m_Host;

    /**
     * @var Integer $m_Port LDAP port
     */
    private $m_Port;

    /**
     * @var bool $m_Use_SSL Set to true to use SSL (LDAPS)
     */
    private $m_Use_SSL;

    /**
     * @var bool $m_Use_TLS Set to true to use TSL
     *
     * If you wish to use TLS you should ensure that $m_Use_SSL is
     * set to false and vice-versa
     */
    private $m_Use_TLS;

    /**
     * @var string $m_Root LDAP root
     */
    private $m_Root;

    /**
     * @var string $m_Manager_Base_DN Base for the manager DN
     */
    private $m_Manager_Base_DN;

    /**
     * @var string $m_Manager The LDAP manager (user name only!)
     */
    private $m_Manager;

    /**
     * @var string $m_Password The ldap password
     */
    private $m_Password;

    /**
     * @var string $m_User_Search_DN User search DN (without ldap root)
     */
    private $m_User_Search_DN;

    /**
     * @var string $m_Manager_OU LDAPAuthenticator manager OU: used
     * in case the Ldap_Manager is in some special OU that distinguishes
     * it from the other users.
     */
    private $m_Manager_OU;

    /**
     * @var \array $m_Valid_Groups Array of valid groups to be used to
     * filter the groups to which the user belongs.
     */
    private $m_Valid_Groups;

    /**
     * @var \array Array of authorized groups
     *
     * If $m_AuthorizedGroups is not empty, the groups array returned by
     * adLDAP->user_groups will be intersected with $m_AuthorizedGroups.
     * If the intersection is empty, the user will not be allowed to log in.
     */
    private $m_Authorized_Groups;

    /**
     * Constructor: instantiates an LDAPAuthenticator object with the settings
     * specified in the configuration file.
     */
    public function __construct() {

        global $LDAP_CONFIG;
        global $HRM_LOGGER;

        // Include the configuration file
        include(dirname(__FILE__) . "/../../../../config/auth//ldap_config.inc");

        // Assign the variables
        $this->m_Host = $LDAP_CONFIG['ldap_host'];
        $this->m_Port = $LDAP_CONFIG['port'] ;
        $this->m_Use_SSL = $LDAP_CONFIG['use_ssl'] ;
        $this->m_Use_TLS = $LDAP_CONFIG['use_tls'] ;
        $this->m_Root = $LDAP_CONFIG['root'] ;
        $this->m_Manager_Base_DN = $LDAP_CONFIG['manager_base_DN'] ;
        $this->m_Manager = $LDAP_CONFIG['manager'] ;
        $this->m_Password = $LDAP_CONFIG['password'] ;
        $this->m_User_Search_DN = $LDAP_CONFIG['user_search_DN'] ;
        $this->m_Manager_OU = $LDAP_CONFIG['manager_ou'] ;

        // Check group filters
        if (count($LDAP_CONFIG['valid_groups']) == 0 &&
            count($LDAP_CONFIG['authorized_groups']) > 0) {
            // Copy the array
            $LDAP_CONFIG['valid_groups'] = $LDAP_CONFIG['authorized_groups'];
        }
        $this->m_Valid_Groups =  $LDAP_CONFIG['valid_groups'];
        $this->m_Authorized_Groups =  $LDAP_CONFIG['authorized_groups'];

        // Set the connection to null
        $this->m_Connection = null;

        // Connect
        if ($this->m_Use_SSL == true) {
            $ds = @ldap_connect(
                    "ldaps://" . $this->m_Host, $this->m_Port);
        } else {
            $ds = @ldap_connect($this->m_Host, $this->m_Port);
        }

        if ($ds) {

            // Set the connection
            $this->m_Connection = $ds;

            // Set protocol (and check)
            if (!ldap_set_option($this->m_Connection,
                    LDAP_OPT_PROTOCOL_VERSION, 3)) {
                $HRM_LOGGER->error("Could not set LDAP protocol version to 3.");
            }

            if ($this->m_Use_TLS) {
                if (!ldap_start_tls($ds)) {
                    $HRM_LOGGER->error("Could not activate TLS.");
                }
            }

        } else {

            $HRM_LOGGER->error("Could not connect to $this->m_Host.");
        }
    }

    /**
     * Destructor: closes the connection.
     */
    public function __destruct() {
        if ($this->isConnected()) {
            @ldap_close($this->m_Connection);
        }
    }

    /**
     * Return the email address of user with given username.
     *
     * @param String $uid Username for which to query the email address.
     * @return string String email address or NULL
     */
    public function getEmailAddress($uid) {

        // Bind the manager
        if (!$this->bindManager()) {
            return "";
        }

        // Searching for user $uid
        $filter = "(uid=" . $uid . ")";
        $searchbase = $this->searchbaseStr();
        $sr = @ldap_search(
                $this->m_Connection, $searchbase, $filter, array('uid', 'mail'));
        if (!$sr) {
            return "";
        }
        if (@ldap_count_entries($this->m_Connection, $sr) != 1) {
            return "";
        }
        $info = @ldap_get_entries($this->m_Connection, $sr);
        $email = $info[0]["mail"][0];
        return $email;
    }

    /*!
    \brief
    \param  $username String
    \param  $password String
    \return boolean:
    */
    /**
     * Authenticates the User with given username and password against LDAP.
     *
     * @param String $uid Username for authentication.
     * @param String $userPassword Password for authentication.
     * @return bool True if authentication succeeded, false otherwise.
     */
    public function authenticate($uid, $userPassword) {

        global $HRM_LOGGER;

        if (!$this->isConnected()) {
            $HRM_LOGGER->error("Authenticate -- not connected!");
            return false;
        }

        // This is a weird behavior of LDAP: if the password is empty, the
        // binding succeeds!
        // Therefore we check in advance that the password is NOT empty!
        if (empty($userPassword)) {
            $HRM_LOGGER->error("Authenticate: empty manager password!");
            return false;
        }

        // Bind the manager -- or we won't be allowed to search for the user
        // to authenticate
        if (!$this->bindManager()) {
            return false;
        }

        // Make sure $uid is lowercase
        $uid = strtolower($uid);

        // Is the user active?
        if (!$this->isActive($uid)) {
            return false;
        }

        // Searching for user $uid
        $filter = "(uid=" . $uid . ")";
        $searchbase = $this->searchbaseStr();
        $sr = @ldap_search(
            $this->m_Connection, $searchbase, $filter, array('uid', 'memberof'));
        if (!$sr) {
            $HRM_LOGGER->error("Authenticate -- search failed! " .
                "Search base: \"$searchbase\"");
            return false;
        }
        if (@ldap_count_entries($this->m_Connection, $sr) != 1) {
            $HRM_LOGGER->error("Authenticate -- user not found!");
            return false;
        }

        // Now we try to bind with the found dn
        $result = @ldap_get_entries($this->m_Connection, $sr);
        if ($result[0]) {

            // If this succeeds, the user is authenticated
            $b = @ldap_bind($this->m_Connection, $result[0]['dn'], $userPassword);

            // If authentication failed, we can return here.
            if ($b === false) {
                return false;
            }

            // If it succeeded, fo we need to check for group authorization?
            if (count($this->m_Authorized_Groups) == 0) {
                // No, we don't
                return $b;
            }

            // Test whether at least one of the user groups is contained in
            // the list of authorize groups.
            $groups = $result[0]["memberof"];
            for ($i = 0; $i < count($groups); $i++) {
                for ($j = 0; $j < count($this->m_Authorized_Groups); $j++) {
                    if (strpos($groups[$i], $this->m_Authorized_Groups[$j])) {
                        $HRM_LOGGER->info("User $uid: group authentication succeeded.");
                        return true;
                    }
                }
            }

            // Not found
            $HRM_LOGGER->info("User $uid: user rejected by failed group authentication.");
            return false;
        }
        return false;
    }

    /**
     * Return the group the user with given username belongs to.
     *
     * @param String $uid Username for which to query the group.
     * @return string Group or "" if not found.
     */
    public function getGroup($uid) {

        global $HRM_LOGGER;

        // Bind the manager
        if (!$this->bindManager()) {
            return "";
        }

        // Searching for user $uid
        $filter = "(uid=" . $uid . ")";
        $searchbase = $this->searchbaseStr();
        $sr = @ldap_search($this->m_Connection, $searchbase, $filter,
                array('uid', 'memberof'));
        if (!$sr) {
            $HRM_LOGGER->warning("Group -- no group information found!");
            return "";
        }

        // Get the membership information
        $info = @ldap_get_entries($this->m_Connection, $sr);
        $groups = $info[0]["memberof"];

        // Filter by valid groups?
        if (count($this->m_Valid_Groups) == 0) {

            // The configuration did not specify any valid groups
            $groups = array_diff(
                    explode(',', strtolower($groups[0])),
                    explode(',', strtolower($searchbase)));
            if (count($groups) == 0) {
                return "";
            }
            // Return the first group
            $groups = $groups[0];
            // Remove ou= or cn= entries
            $matches = array();
            if (!preg_match('/^(OU=|CN=)(.+)/i', $groups, $matches)) {
                return "";
            } else {
                if ($matches[2] == null) {
                    return "";
                }
                return $matches[2];
            }
        } else {

            // The configuration contains a list of valid groups
            for ($i = 0; $i < count($groups); $i++) {
                for ($j = 0; $j < count($this->m_Valid_Groups); $j++) {
                    if (strpos($groups[$i], $this->m_Valid_Groups[$j])) {
                        return ($this->m_Valid_Groups[$j]);
                    }
                }
            }
        }
        return "";
    }

    /**
     * Check whether there is a connection to LDAP
     *
     * @return bool True if the connection is up, false otherwise
     */
    public function isConnected() {
        return ($this->m_Connection != null);
    }

    /**
     * Returns the last occurred error
     *
     * @return string last LDAP error
     */
    public function lastError() {
        if ($this->isConnected()) {
            return @ldap_error($this->m_Connection);
        } else {
            return "";
        }
    }

    /**
     * Binds LDAP with the configured manager for queries to
     * be possible.
     * @return bool True if the manager could bind, false otherwise
     */
    private function bindManager() {

        global $HRM_LOGGER;

        if (!$this->isConnected()) {
            return false;
        }

        // Search DN
        $dn = $this->dnStr();

        // Bind
        $r = @ldap_bind($this->m_Connection, $dn, $this->m_Password);
        if ($r) {
            return true;
        }

        // If binding failed, we report
        $HRM_LOGGER->error("Binding: binding failed! " .
                "Search DN: \"$dn\"");
        return false;
    }

    /**
     * Create the search base string.
     *
     * @return string Search base string
     */
    private function searchbaseStr() {
        return ($this->m_User_Search_DN . "," . $this->m_Root);
    }

    /**
     * Create the DN string
     *
     * @return string DN string
     */
    private function dnStr() {
        $dn = $this->m_Manager_Base_DN . "=" .
                $this->m_Manager . "," .
                $this->m_Manager_OU . "," .
                $this->m_User_Search_DN . "," .
                $this->m_Root;
        // Since m_Manager_OU can be empty, we make sure not
        // to have double commas
        $dn = str_replace(',,', ',', $dn);
        return $dn;
    }

}
