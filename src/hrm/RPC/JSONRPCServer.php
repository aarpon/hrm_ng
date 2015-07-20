<?php
/*

Server implementing the JSON-RPC (version 2.0) protocol.

This is an example Javascript code to interface with json-rpc-server.php:

01:    <script type="text/javascript">
02:        $(document).ready($('#button').click(function() {
03:            JSONRPCRequest({
04:                method : 'isLoggedIn',
05:                params : {userName : 'name'}
06:            }, function(data) {
07:                $('#report').html("<b>" + data['result'] + "</b>");
08:            });
09:        }));
10:    </script>

Passing parameters to the Ajax method is very flexible (line 5). The recommended method is:

                  params : {parameter : 'value'}

for one parameter, and:

                  params : {parameterOne : 'valueOne',
                            parameterTwo : 'valueTwo'}

for more parameters. If a parameter is an array, use:


                  params : {parameter : ['valueOne', 'valueTwo', 'valueThree']}

In PHP, the parameters can then be retrieved with:

                  $params = $_POST['params'];

===

For illustration, the following is also possible:

For a single value:

Javascript:   params : 'ExcitationWavelength'
PHP:          $params := "ExcitationWavelength"

For a vector:

Javascript:   params : ['ExcitationWavelength', 'EmissionWavelength']
PHP:          $params[0] := "ExcitationWavelength"
              $params[1] := "EmissionWavelength"

*/

namespace hrm\RPC;
use hrm\User\Base\UserQuery;

require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Class JSONRPCServer SIMPLE JSON RPC Server class.
 * @package hrm\RPC
 */
class JSONRPCServer
{
    /**
     * @var $sessionManager \hrm\RPC\SessionManager Simple session manager.
     */
    private $sessionManager;

    /**
     * @var string HTTP response status
     */
    private $httpResponseStatus = "400 Bad Request";

    /**
     * @var Array HTTP response content.
     */
    private $httpResponseArray = array(
        "id" => -1,
        "result" => array(),
        "success" => false,
        "message" => "");

    /**
     * @var string Response content type: "application/json;charset=utf8"
     */
    private $httpResponseContentType = "application/json;charset=utf8";

    /**
     * @var Array JSON RPC request sent from the client (converted to a PHP
     * array).
     */
    private $httpRequest = null;

    /**
     * @var bool Flag that indicates whether the JSON RPC request is valid.
     */
    private $isRequestValid = false;

    /**
     * @var int|string PHP session ID sent from the client.
     */
    private $clientId = -1;

    /**
     * @var string Invoked method.
     */
    private $method = "";

    /**
     * @var null|array null of array of parameters for the invoked method.
     */
    private $params = null;

    /**
     * @var array User roles and their numeric mappings:
     *
     * user: 0
     * manager: 1
     * admin: 2
     */
    private static $USER_ROLES = array(
        "user"    => 0,
        "manager" => 1,
        "admin"   => 2
    );

    /**
     * @const
     * @var array Array of known and valid methods with the minimum user
     *            role required for running it.§
     */
    private static $VALID_METHODS = array(
        "logIn"   => 0,           // Everyone
        "logOut"  => 0,           // Everyone
        "addUser" => 1);          // Manager or higher

    /**
     * Constructor
     * @param $request Array JSON RPC request from the client converted to
     * a PHP array.
     * @param $phpSessionID string Currently active session ID.
     *
     * The session ID must be retrieved from $_SESSION and passed
     * on as argument to the constructor.
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
     */
    public function executeRequest()
    {
        // Can the user execute the method?
        if (! $this->canUserRunMethod())
        {
            $this->setFailure(-1, array(),
                "The user is not allowed to run this method.",
                "401 Method Not Allowed");
            return;
        }

        // Call the method
        $this->{$this->method}();
    }

    /**
     * Returns the validity of the request.
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
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
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
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
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
     * The response status is a JSON array.
     *
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     *
     * @return string response content (a JSON array).
     */
    public function getResponseContentType()
    {
        return $this->httpResponseContentType;
    }

    /**
     * Return the response content length.
     *
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     *
     * @return string response content (a JSON array).
     */
    public function getResponseContentLength()
    {
        return mb_strlen(json_encode($this->httpResponseArray));
    }

    /**
     * Validate the JSON RPC request from the client.
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
            $this->setFailure(-1, array(), "Invalid JSON-RPC 2.0 call.",
                "400 Bad Request");

            // Return here
            return;

        };

        // The session ID must be present. If a session does not yet exist,
        // it should be set to -1.
        if (!(array_key_exists('id', $this->httpRequest))) {

            // Flag invalid
            $this->isRequestValid = false;

            // Set the response to failure
            $this->setFailure(-1, array(), "Session ID is missing!",
                "400 Bad Request");

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
            $this->setFailure(-1, array(), "Method missing!",
                "400 Bad Request");

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
            $this->setFailure(-1, array(), "Invalid method!",
                "400 Bad Request");

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
                $this->setFailure(-1, array(), "Invalid parameters!",
                    "400 Bad Request");

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
     * @throws Exception if the requested method is invalid. This should not
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
                // TODO: Complete
                return false;

            default:

                // Unknown method. This is a bug!
                throw new Exception("Invalid method requested!");
        }
    }

    /**
     * Check whether currently logged in User has the permissions to run
     * the requested method.
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
     * @param $id string|integer Session ID
     * @param $result Array Result from the method call.
     * @param $message string Message returned by the method call.
     * @param string $responseStatus HTTP/1.1 response status. Default for
     *               success is "200 OK".
     * @return Array JSON object.
     */
    private function setSuccess($id, $result = array(), $message = "",
                                $responseStatus = "200 OK")
    {
        // Initialize and fill the array
        $this->httpResponseArray = array(
            "id" => $id,
            "success" => true,
            "result" => $result,
            "message" => $message);

        // Set the response code
        $this->httpResponseStatus = $responseStatus;
    }

    /**
     * Fills the response array in case of failure.
     *
     * @see setSuccess for a definition of success and failure of a method call.
     *
     * @param $id string|integer Session ID
     * @param $result Array Result from the method call.
     * @param $message string Message returned by the method call.
     * @param string $responseStatus HTTP/1.1 response status. Default for
     *               failure is "400 Bad Request".
     * @return Array JSON object.
     */
    function setFailure($id, $result = array(), $message = "",
                        $responseStatus = "400 Bad Request")
    {
        // Initialize and fill the array
        $this->httpResponseArray = array(
            "id" => $id,
            "success" => false,
            "result" => $result,
            "message" => $message);

        // Set the response code
        $this->httpResponseStatus = $responseStatus;
    }

    /*
     *
     * METHOD IMPLEMENTATIONS
     *
     */
    private function logIn()
    {
        // Prepare the result
        $result = array("success" => null, "role" => null);

        // Query the User
        $user = UserQuery::create()->findOneByName($this->params["username"]);
        if (null === $user) {

            // Fill the result array
            $result["success"] = false;
            $result["role"] = null;

            // The User does not exist!
            $this->setFailure(-1, $result, "The user does not exist.",
                "401 Unauthorized");

        }

        // Try authenticating the user.
        if ($user->logIn($this->params["password"])) {

            // Start a new session
            $this->sessionManager->restart();

            // Fill in the result array
            $result["success"] = true;
            $result["role"] = $user->getRole();

            // Successful login
            // TODO: Set http response status
            $this->setSuccess($this->sessionManager->getSessionID(), $result,
                "The user was logged in successfully.",
                "200 OK");

            // Store the User ID in the PHP session
            $this->sessionManager->set('UserID', $user->getId());

        } else {

            // Fill in the result array
            $result["success"] = false;
            $result["role"] = null;

            // Set success (although the user could not be authenticated).
            // TODO: Set http response status
            $this->setSuccess(-1, $result,
                "The user could not be logged in.",
                "401 Unauthorized");
        }
    }

    /**
     * Log out current User
     * @return bool True if the User could be logged out; false otherwise.
     */
    private function logOut()
    {
        // Is the session active?
        if (! $this->sessionManager->isActive($this->clientId))
        {
            // The session was not active and/or no User was logged in.
            $this->setFailure(-1, false,
                $this->sessionManager->lastMessage(),
                "400 Bad request");

        } else {

            // Destroy current session
            $this->sessionManager->destroy();

            // Report success
            $this->setSuccess(-1, true,
                "The user was logged out successfully.",
                "200 OK");

        }
    }

}

