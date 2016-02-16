<?php

namespace hrm\RPC;
use hrm\User\Base\UserQuery;
use hrm\User\User;
use Propel\Runtime\Exception\PropelException;

require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Server implementing the JSON-RPC (version 2.0) protocol.
 *
 * # Output header
 *
 * The `html/ajax/json-rpc-server.php` scripts processes the asynchronous
 * requests submitted by the clients using an object of class
 * `\hrm\RPC\JSONRPCServer` and returns an HTTP/1.1 header set up as follows:
 *
 * ```php
 * 01:  header("HTTP/1.1 " . $server->getResponseStatus());
 * 02:  header("Content-type: " . $server->getResponseContentType(), true);
 * 03:  header("Content-length: " . $server->getResponseContentLength());
 * 04:  echo $server->getResponseBody();
 * ```
 *
 * - Line 01 returns a string in the form: ``"200 OK"``, ``"400 Bad Request"``,
 *   ``401 Unauthorized"``.
 *     - "See full list at: http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html
 * - Line 02 returns following string: ``"application/json;charset=utf8"``.
 * - Line 03 returns the length of the JSON-encoded response from line 04.
 * - Line 04 sends a JSON-encoded PHP array as described below (Output).
 *
 * # Structure of JSON objects
 *
 * ## Input
 *
 * The JSON object sent by the client to the server must have following
 * structure:
 *
 * ```javascript
 * {
 *     "method": “<method_name>”,
 *     "params": {
 *         "<key1>": “<value1>”,
 *         "<key2>": “<value2>”,
 *         …
 *         "<keyN>": “<valueN>”,
 *     },
 *     "id": <valid_PHP_session_id | -1>,
 *     "jsonrpc": "2.0"
 * }
 * ```
 *
 * - If there is currently no active PHP session (such as before the first login),
 *   the ``id``field must be set to ``-1``.
 * - The properties ``method``, ``id`` and ``jsonrpc`` MUST always be present;
 *   ``params`` is optional.
 *
 * ### Example javascript call
 *
 * This is a trivial example of how an element in an HTML page can send a JSON
 * request to the server and process the response.
 *
 * ```javascript
 * <script type="text/javascript">
 *     $(document).ready($('#button').click(function() {
 *         JSONRPCRequest({
 *             id     : -1,
 *             method : 'logIn',
 *             params : {
 *                 username : '<username>',
 *                 password : '<password>'
 *             },
 *             jsonrpc : '2.0'
 *         }, function(data) {
 *             $('#report').html("<b>" + data['result'] + "</b>");
 *         });
 *     }));
 * </script>
 * ```
 *
 * The ``JSONRPCRequest()`` javascript function referenced above can for
 * instance be implemented like this:
 *
 * ```javascript
 * function JSONRPCRequest(data, callback) {
 *
 *     "use strict"
 *     data.id = "1";
 *     data.jsonrpc = "2.0";
 *
 *     $.ajaxSetup({
 *         cache: false
 *     });
 *
 *     $.ajax(
 *     {
 *         url: "ajax/json-rpc-server.php",
 *         type: "POST",
 *         dataType: "json",
 *         async: true,
 *         data: data,
 *         success: callback
 *     });
 * }
 * ```
 *
 * ## Output
 *
 * The JSON object sent by the server to the client must have following
 * structure:
 *
 * ```javascript
 * {
 *     "id": <PHP_sessionid | -1>,
 *     "result": {
 *         "<key1>": “<value1>”,
 *         "<key2>": “<value2>”,
 *         …
 *         "<keyN>": “<valueN>”,
 *     },
 *     "error": false,
 *     "message": "<A human-friendly message>"
 * }
 * ```
 *
 * ### id
 *
 * If there is currently no active PHP session (such as right after a logout),
 * the ``id`` field must be set to ``-1``. Otherwise, the session id is returned
 * to the client. The client will pack that same id in the next request.
 *
 * ### error
 *
 * The property ``error`` (boolean) is used to indicate that the call could not
 * be executed for one of the following reasons:
 *
 * - there is no active session (i.e. no user is logged in)
 * - the user's privileges are not high enough (e.g. a normal user tries to
 *   execute a call that requires administrator privileges)
 * - something unexpected happened (e.g. the database server is unreachable)
 *
 *
 * Do not use ``error`` to indicate failed authentication (e.g. because the
 * password was wrong, or because the user does not exist in the database).
 *
 * ### result
 *
 * All results of normal operations must be stored in ``result``, which must be
 * an array (more precisely a key-value map).
 *
 * - If there is no result, i.e. in the case of an invalid request from the
 *   client, ``result`` must be set to be an empty array.
 *
 * ### message
 *
 * The ``message`` string contains a human-friendly answer to the method (e.g.
 * “The user was logged in successfully.”
 *
 * In case of ``error``, use ``message`` to return an explanation of what went
 * wrong (e.g. "User is not allowed to delete another user.", “Fatal error:
 * database not reachable”, or “Fatal error: disk is full”, …).
 *
 * @package hrm\RPC
 */
class JSONRPCServer
{
    /**
     * Simple session manager.
     * @var \hrm\RPC\SessionManager $sessionManager
     */
    private $sessionManager;

    /**
     * HTTP response status
     * @var string $httpResponseStatus
     */
    private $httpResponseStatus = "400 Bad Request";

    /**
     * HTTP response content.
     * @var array $httpResponseArray
     */
    private $httpResponseArray = array(
        "id" => -1,
        "result" => array(),
        "success" => false,
        "message" => "");

    /**
     * Response content type: "application/json;charset=utf8"
     * @const
     * @var string $httpResponseContentType
     */
    private $httpResponseContentType = "application/json;charset=utf8";

    /**
     * JSON RPC request sent from the client (converted to a PHP array).
     * @var array $httpRequest
     */
    private $httpRequest = null;

    /**
     * Flag that indicates whether the JSON RPC request is valid.
     * @var bool $isRequestValid
     */
    private $isRequestValid = false;

    /**
     * PHP session ID sent from the client.
     * @var int|string $clientId
     */
    private $clientId = -1;

    /**
     * Invoked method.
     * @var string $method
     */
    private $method = "";

    /**
     * null or array of parameters for the invoked method.
     * @var null|array $params
     */
    private $params = null;

    /**
     * User roles and their numeric mappings.
     *
     * The user roles are mapped to an integer value as follows:
     *
     * - user: 0
     * - manager: 1
     * - admin: 2
     *
     *
     * Incomplete list of permissions per role:
     *
     * - user:
     *   - can use the HRM
     *   - can manage own files
     *   - can create templates
     *   - can launch any type of jobs
     *   - can share templates with other users and managers/admins
     * - manager:
     *   - can do everything the user can do, plus:
     *   - can manage users
     *   - can create global templates
     * - admin:
     *   - can do everything the manager can do, plus:
     *   - can configure the HRM
     *   - can clean the HRM Queue
     *   - can get the status of the server
     *   - can get the status of the database
     *   - can relaunch the queue manager
     *
     * @const
     * @var array $USER_ROLES
     */
    private static $USER_ROLES = array(
        "user"    => 0,
        "manager" => 1,
        "admin"   => 2
    );

    /**
     * Array of known and valid methods with the minimum user role required for
     * running it.
     * @const
     * @var array $VALID_METHODS
     */
    private static $VALID_METHODS = array(
        "logIn"      => 0,
        "logOut"     => 0,
        "addUser"    => 1,
        "deleteUser" => 1);

    /**
     * Array of status codes for the HTTP/1.1 response.
     *
     * The key is the numeric code, the value is the complete status to be
     * packed in the header.
     *
     * ```php
     * $RESPONSE_STATUS_CODES = array(
     *    200 => "200 OK",
     *    400 => "400 Bad Request",
     *    401 => "401 Unauthorized",
     *    405 => "405 Method Not Allowed",
     *    500 => "500 Internal Server Error");
     * ```
     *
     * @const
     * @var array $RESPONSE_STATUS_CODES
     *
     * @see setResponseToSuccess, setResponseToFailure
     */
    private static $RESPONSE_STATUS_CODES = array(
        200 => "200 OK",
        400 => "400 Bad Request",
        401 => "401 Unauthorized",
        405 => "405 Method Not Allowed",
        500 => "500 Internal Server Error");

    /**
     * Constructor.
     *
     * The constructor will initialize the session by instantiating its private
     * SessionManager member.
     *
     * @see \hrm\RPC\SessionManager
     * @param array $request JSON RPC request from the client converted to
     * a PHP array.
     */
    public function __construct(array $request)
    {
        // Instantiate the SessionManager
        $this->sessionManager = new SessionManager();

        // Make sure the $request is an array
        if (is_string($request))
        {
            $request = json_decode($request);
        }

        // Store the request
        $this->httpRequest = $request;

        // Validate it
        $this->validateRequest();
    }

    /**
     * Execute the method.
     *
     * The method was passed to the constructor as part of the JSON request.
     *
     * If the user is not allowed to run the requested method, the output
     * response will be:
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {},
     *    "error": true,
     *    "message": "The user is not allowed to run this method."
     * }
     * ```
     *
     * HTTP response code: ``405 Method Not Allowed``
     */
    public function executeRequest()
    {
        // Can the user execute the method?
        if (! $this->canUserRunMethod())
        {
            $this->setResponseToFailure(
                -1,
                array(),
                "The user is not allowed to run this method.",
                405);
            return;
        }

        // Call the method
        $this->{$this->method}();
    }

    /**
     * Returns the validity of the request.
     *
     * The passed JSON request is tested in the constructor, and this method is
     * used to query its validity.
     *
     * @return bool True if the request is valid, false otherwise.
     */
    public function isRequestValid()
    {
        return $this->isRequestValid;
    }

    /**
     * Return the response status.
     *
     * The response status is something in the form: "200 OK"
     *
     * You can build the HTTP header as follows:
     *
     * ```php
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     * ```
     *
     * @return string response status.
     */
    public function getResponseStatus()
    {
        return $this->httpResponseStatus;
    }

    /**
     * Return the response body.
     *
     * The response status is a JSON array.
     *
     * You can build the HTTP header as follows:
     *
     * ```php
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     * ```
     *
     * @return string response content (a JSON array).
     */
    public function getResponseBody()
    {
        return json_encode($this->httpResponseArray);
    }

    /**
     * Return the response content type.
     *
     * The response status is "application/json;charset=utf8".
     *
     * You can build the HTTP header as follows:
     *
     * ```php
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     * ```
     *
     * @return string response content ("application/json;charset=utf8").
     */
    public function getResponseContentType()
    {
        return $this->httpResponseContentType;
    }

    /**
     * Return the response content length.
     *
     * You can build the HTTP header as follows:
     *
     * ```php
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     * ```
     *
     * @return int length of the response content.
     */
    public function getResponseContentLength()
    {
        return mb_strlen(json_encode($this->httpResponseArray));
    }

    /**
     * Validate the JSON RPC request from the client.
     *
     * If the request is not valid, the output JSON response will be one of the
     * following.
     *
     * <u>The call is not a valid JSON RPC 2.0 call</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {},
     *    "error": true,
     *    "message": "Invalid JSON-RPC 2.0 call."
     * }
     * ```
     *
     * HTTP response code: ``400 Bad Request``
     *
     * <u>The session ID is not present</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {},
     *    "error": true,
     *    "message": "Session ID is missing."
     * }
     * ```
     *
     * HTTP response code: ``400 Bad Request``
     *
     * <u>The request does not specify a method to run</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {},
     *    "error": true,
     *    "message": "Method missing."
     * }
     * ```
     *
     * HTTP response code: ``400 Bad Request``
     *
     * <u>The requested method does not exist</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {},
     *    "error": true,
     *    "message": "Invalid method."
     * }
     * ```
     *
     * HTTP response code: ``400 Bad Request``
     *
     * <u>The method parameters are invalid</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {},
     *    "error": true,
     *    "message": "Invalid parameters."
     * }
     * ```
     *
     * HTTP response code: ``400 Bad Request``
     *
     */
    private function validateRequest()
    {
        // Make sure the request valid status is false
        $this->isRequestValid = false;

        // Do we have a JSON-RPC 2.0 request?
        if (!(array_key_exists('jsonrpc', $this->httpRequest) &&
            $this->httpRequest['jsonrpc'] == "2.0")) {

            // Flag invalid
            $this->isRequestValid = false;

            // Set the response to failure
            $this->setResponseToFailure(
                -1,
                array(),
                "Invalid JSON-RPC 2.0 call.",
                400);

            // Return here
            return;

        };

        // The session ID must be present. If a session does not yet exist,
        // it should be set to -1.
        if (!(array_key_exists('id', $this->httpRequest))) {

            // Flag invalid
            $this->isRequestValid = false;

            // Set the response to failure
            $this->setResponseToFailure(
                -1,
                array(),
                "Session ID is missing.",
                400);

            // Return here
            return;
        }

        // Retrieve the session ID from the client
        $this->clientId = $this->httpRequest['id'];

        // The method is mandatory
        if (!isset($this->httpRequest['method'])) {

            // Flag invalid
            $this->isRequestValid = false;

            // Set the response to failure
            $this->setResponseToFailure(
                -1,
                array(),
                "Method missing.",
                400);

            // Return here
            return;
        }

        // Store the method
        $this->method = $this->httpRequest['method'];

        // Check that the method is valid
        if (! $this->isMethodValid())
        {
            // Flag invalid
            $this->isRequestValid = false;

            // Set the response to failure
            $this->setResponseToFailure(
                -1,
                array(),
                "Invalid method.",
                400);

            // Return here
            return;
        }

        // Method parameters
        $this->params = null;
        if (array_key_exists('params', $this->httpRequest)) {

            // Set the parameters
            $this->params = $this->httpRequest['params'];

            // Che that the parameters are valid
            if (! $this->areParametersValid())
            {
                // Flag invalid
                $this->isRequestValid = false;

                // Set the response to failure
                $this->setResponseToFailure(
                    -1,
                    array(),
                    "Invalid parameters.",
                    400);

                // Return here
                return;
            }
        }

        // Now the valid status can be set to true
        $this->isRequestValid = true;

    }

    /**
     * Check if the method is valid.
     * @return bool True if the method is valid, false otherwise.
     */
    private function isMethodValid()
    {
        return (array_key_exists($this->method, self::$VALID_METHODS));
    }

    /**
     * Check if the parameters for currently set method are valid.
     * @return bool True if the parameters are valid, false otherwise.
     * @throws \Exception if the requested method is invalid. This should not
     * happen, since the call to areParametersValid() should follow a call to
     * isMethodValid().
     */
    private function areParametersValid()
    {
        // Check the parameters for current method
        switch ($this->method) {

            case "logIn":

                // logIn needs 'username' and 'password'
                return (array_key_exists('username', $this->params) &&
                    array_key_exists('password', $this->params));

            case "logOut":

                // logOut only needs a valid client ID
                return $this->clientId != -1;

            case "addUser":

                // Make sure the expected parameters exist
                return (array_key_exists('username', $this->params) &&
                    array_key_exists('password', $this->params) &&
                    array_key_exists('email', $this->params) &&
                    array_key_exists('research_group', $this->params) &&
                    array_key_exists('role', $this->params) &&
                    array_key_exists('password', $this->params));

            case "deleteUser":

                // deleteUser only needs 'username'
                return (array_key_exists('username', $this->params));

            default:

                // Unknown method. This is a bug!
                throw new \Exception("Invalid method requested!");
        }
    }

    /**
     * Check whether currently logged in User has the permissions to run
     * the requested method.
     *
     * @return bool True if the user can run the method, false otherwise.
     */
    private function canUserRunMethod()
    {
        // The logIn and logOut methods can always be run
        if ($this->method == "logIn" || $this->method == "logOut") {
            return true;
        }

        // Check it the UserID is stored in the session
        $userID = $this->sessionManager->get('UserID');
        if ($userID !== null) {

            // Try retrieving the user
            $user = UserQuery::create()->findOneById($userID);
            if (null === $user) {
                return false;
            }

            // Get User role
            $role = $user->getRole();

            if ($role < self::$USER_ROLES[$this->method]) {
                return false;
            }

            return true;
        }

        // If the user does not exist, the method cannot be run.
        return false;
    }

    /**
     * Fills the response array in case of success.
     *
     * Please mind that a success is defined as a successful execution of the
     * requested method. For example, if the user credentials are wrong in an
     * attempt to login the user, the method will return success, even though
     * the result of the login attempt will be negative.
     *
     * @param string|integer $id Session ID
     * @param array $result Result from the method call.
     * @param string $message Message returned by the method call.
     * @param int $responseCode HTTP/1.1 response status. Default for
     *               success is 200.
     * @return array JSON object.
     */
    private function setResponseToSuccess($id, $result = array(), $message = "",
                                          $responseCode = 200)
    {
        // Initialize and fill the array
        $this->httpResponseArray = array(
            "id" => $id,
            "success" => true,
            "result" => $result,
            "message" => $message);

        // Set the response code
        $this->httpResponseStatus = self::$RESPONSE_STATUS_CODES[$responseCode];
    }

    /**
     * Fills the response array in case of failure.
     *
     * @see setResponseToSuccess for a definition of success and failure of a
     * method call.
     *
     * @param string|integer $id Session ID
     * @param array $result Result from the method call.
     * @param string $message Message returned by the method call.
     * @param int $responseCode HTTP/1.1 response status. Default for
     *               failure is 400.
     * @return array JSON object.
     */
    private function setResponseToFailure($id, $result = array(), $message = "",
                                  $responseCode = 400)
    {
        // Initialize and fill the array
        $this->httpResponseArray = array(
            "id" => $id,
            "success" => false,
            "result" => $result,
            "message" => $message);

        // Set the response code
        $this->httpResponseStatus = self::$RESPONSE_STATUS_CODES[$responseCode];
    }

    /*
     *
     * METHOD IMPLEMENTATIONS
     *
     */

    /**
     * Method: User log in
     *
     * <b>Minimum role</b>
     *
     * ``user``
     *
     * <b>Input request</b>
     *
     * ```javascript
     * {
     *    "method": “logIn”,
     *    "params": {
     *       "username": “<username>”,
     *       "password": “<password>”
     *   },
     *   "id": -1,
     *   "jsonrpc": "2.0"
     * }
     * ```
     *
     * <b>Output response</b>
     *
     * There are three cases. For each, the following lists the returned JSON
     * objects.
     *
     * <u>Both username and password are wrong</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {
     *      "loggedIn": false,
     *      "role": null
     *    },
     *    "error": false,
     *    "message": "The user does not exist."
     * }
     * ```
     *
     * <u>Username is correct but password is wrong</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {
     *      "loggedIn": false,
     *      "role": null
     *    },
     *    "error": false,
     *    "message": "The user could not be logged in."
     * }
     * ```
     *
     * <u>Both username and password are correct</u>
     *
     * ```javascript
     * {
     *    "id": <PHP_sessionid>,
     *    "result": {
     *      "loggedIn": true,
     *      "role":”user|manager|admin”
     *    },
     *    "error": false,
     *    "message": "The user was logged in successfully."
     * }
     * ```
     *
     * @todo Capture exceptions and return ``error`` with the exception
     *       ``message``.
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    private function logIn()
    {
        // Prepare the result
        $result = array("loggedIn" => null, "role" => null);

        // Query the User
        $user = UserQuery::create()->findOneByName($this->params["username"]);
        if (null === $user) {

            // Fill the result array
            $result["loggedIn"] = false;
            $result["role"] = null;

            // The User does not exist!
            $this->setResponseToSuccess(
                -1,
                $result,
                "The user does not exist.",
                401);

        }

        // Try authenticating the user.
        if ($user->logIn($this->params["password"])) {

            // Start a new session
            $this->sessionManager->restart();

            // Fill in the result array
            $result["loggedIn"] = true;
            $result["role"] = $user->getRole();

            // Successful login
            $this->setResponseToSuccess(
                $this->sessionManager->getSessionID(),
                $result,
                "The user was logged in successfully.",
                200);

            // Store the User ID in the PHP session
            $this->sessionManager->set('UserID', $user->getId());

        } else {

            // Fill in the result array
            $result["loggedIn"] = false;
            $result["role"] = null;

            // Set success (although the user could not be authenticated).
            $this->setResponseToSuccess(
                -1,
                $result,
                "The user could not be logged in.",
                401);
        }
    }

    /**
     * Method: User log out
     *
     * <b>Minimum role</b>
     *
     * ``user``
     *
     * <b>Input request</b>
     *
     * This method takes no parameters.
     *
     * ```javascript
     * {
     *   "method": “logOut”,
     *   "id": <PHP_sessionID>,
     *   "jsonrpc": "2.0"
     * }
     * ```
     *
     * <b>Output response</b>
     *
     * There are two cases. For each, the following lists the returned JSON
     * objects.
     *
     * <u>There was no currently logged in user (and no active session)</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {
     *      “loggedOut”: true,
     *      “previousId”: -1
     *    },
     *    "error": true,
     *    "message": "Invalid client session id."
     * }
     * ```
     *
     * <u>The logout was successful</u>
     *
     * ```javascript
     * {
     *    "id": -1,
     *    "result": {
     *      “loggedOut”: true,
     *      “previousId”: <session_id_before_logout>
     *    },
     *    "error": false,
     *    "message": "The user was logged out successfully."
     * }
     * ```
     *
     * @return bool True if the User could be logged out; false otherwise.
     */
    private function logOut()
    {
        // Prepare the result
        $result = array("loggedOut" => null, "previousId" => null);

        // Is the session active?
        if (! $this->sessionManager->isActive($this->clientId))
        {
            // Update result
            $result["loggedOut"] = true;
            $result["previousId"] = -1;

            // The session was not active and/or no User was logged in.
            $this->setResponseToFailure(
                -1,
                $result,
                $this->sessionManager->lastMessage(),
                400);

        } else {

            // Update result
            $result["loggedOut"] = true;
            $result["previousId"] = session_id();

            // Destroy current session
            $this->sessionManager->destroy();

            // Report success
            $this->setResponseToSuccess(
                -1,
                $result,
                "The user was logged out successfully.",
                200);
        }
    }

    /**
     * Method: Add User
     *
     * <b>Minimum role</b>
     *
     * ``manager``
     *
     * <b>Input request</b>
     *
     * ```javascript
     * {
     *    "method": “addUser”,
     *    “params”: {
     *      “username”: “<username>”,
     *      “password”: “<password>”,
     *      “research_group”: “<research_group>”,
     *      “email”: “<email_address>”,
     *      “role”: “user|manager|admin”,
     *      “authentication”; “integrated|active_dir|ldap”,
     *      “remark”: “<remark>”
     *    },
     *    "id": <PHP_sessionID>,
     *    "jsonrpc": "2.0"
     * }
     * ```
     *
     * <b>Output response</b>
     *
     * <u>The new user could not be added because the requesting user does not
     *    have the privileges to do so</u>
     *
     * ```javascript
     * {
     *    "id": <PHP_sessionID>,
     *    "result": {
     *      “userAdded”: false,
     *    },
     *    "error": true,
     *    "message": "Current user is not allowed to add a new user."
     * }
     * ```
     *
     * HTTP response code: ``400 Bad Request``
     *
     * <u>The new user could not be added because a user with current name
     *    already exists</u>
     *
     * ```javascript
     * {
     *    "id": <PHP_sessionID>,
     *    "result": {
     *      “userAdded”: false,
     *    },
     *    "error": false,
     *    "message": "A user with the same name already exists."
     * }
     * ```
     *
     * * HTTP response code: ``400 Bad Request``
     *
     * <u>The new user was successfully added</u>
     *
     * ```javascript
     * {
     *    "id": <PHP_sessionID>,
     *    "result": {
     *      “userAdded”: true,
     *    },
     *    "error": false,
     *    "message": "The user was successfully added."
     * }
     * ```
     *
     * * HTTP response code: ``200 OK``
     *
     * @todo The value for the “authentication” parameter obviously depends on
     *       what has been configured by the administrator. We should decide how
     *       this information is delivered to the client.
     */
    private function addUser()
    {
        // Prepare the result
        $result = array("userAdded" => null);

        // Check whether a User with the same name already exists
        $user = UserQuery::create()->findOneByName($this->params["username"]);
        if (null === $user) {

            # Create user
            $user = new User();

            # Set all properties
            $user->setName($this->params["username"]);
            $user->setPasswordHash($this->params["password"]);
            $user->setEmail($this->params["email"]);
            $user->setResearchGroup($this->params["research_group"]);
            $user->setAuthentication("integrated");
            $user->setRole($this->params["role"]);
            $user->setCreationDate(new \DateTime());
            $user->setLastAccessDate(new \DateTime());
            $user->setStatus("active");

            # Save user
            if ($user->save()) {

                # Report success
                $result["userAdded"] = true;
                $this->setResponseToSuccess(
                    $this->sessionManager->getSessionID(),
                    $result,
                    "The user was successfully added.",
                    200);

            } else {

                # Report failure (server error)
                $result["userAdded"] = false;
                $this->setResponseToFailure(
                    $this->sessionManager->getSessionID(),
                    $result,
                    "There was a problem adding the user.",
                    500);

            }

        } else {

            // The User already exists.
            $result["userAdded"] = false;

            # Return failure
            $this->setResponseToFailure(
                $this->sessionManager->getSessionID(),
                $result,
                "A user with this username already exists.",
                400);
        }

    }

    /**
     * Method: Delete User
     *
     * <b>Minimum role</b>
     *
     * ``manager``
     *
     * <b>Input request</b>
     *
     * ```javascript
     * {
     *    "method": “deleteUser”,
     *    “params”: {
     *      “username”: “<username>”,
     *    },
     *    "id": <PHP_sessionID>,
     *    "jsonrpc": "2.0"
     * }
     * ```
     *
     * <b>Output response</b>
     *
     * <u>The requested user does not exist</u>
     *
     * ```javascript
     * {
     *    "id": <PHP_sessionID>,
     *    "result": {
     *      “userDeleted”: false,
     *    },
     *    "error": true,
     *    "message": "The requested user does not exist."
     * }
     * ```
     *
     * <u>The requested user could not be deleted</u>
     *
     * ```javascript
     * {
     *    "id": <PHP_sessionID>,
     *    "result": {
     *      “userDeleted”: false,
     *    },
     *    "error": true,
     *    "message": "The user could not be deleted."
     * }
     * ```
     *
     * <u>The requested user could not be deleted</u>
     *
     * ```javascript
     * {
     *    "id": <PHP_sessionID>,
     *    "result": {
     *      “userDeleted”: true,
     *    },
     *    "error": false,
     *    "message": "The user was successfully deleted."
     * }
     * ```
     *
     */
    private function deleteUser()
    {
        // Prepare the result
        $result = array("userDeleted" => null);

        // Check whether a User with the given name really exists
        $user = UserQuery::create()->findOneByName($this->params["username"]);
        if (null === $user) {

            # Return failure (server error)
            $result["userDeleted"] = false;
            $this->setResponseToFailure(
                $this->sessionManager->getSessionID(),
                $result,
                "The requested user does not exist.",
                500);

        } else {

            // Delete the user
            try {

                // The delete() function does not return anything.
                // It just throws an exception if the user was
                // already deleted.
                $user->delete();

                // The User was successfully deleted.
                $result["userDeleted"] = true;

                # Return success
                $this->setResponseToSuccess(
                    $this->sessionManager->getSessionID(),
                    $result,
                    "The user was successfully deleted.",
                    200);

            } catch (PropelException $e) {

                // The User could not be deleted.
                $result["userDeleted"] = false;

                # Return failure (server error)
                $this->setResponseToFailure(
                    $this->sessionManager->getSessionID(),
                    $result,
                    "The user could not be deleted.",
                    500);

            }
        }

    }
}

