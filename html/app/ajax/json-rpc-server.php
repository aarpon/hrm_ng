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

namespace hrm;

// Bootstrap
use hrm\User\Base\UserQuery;

require_once dirname(__FILE__) . '/../../src/bootstrap.php';

// This is needed when checking the (session) id submitted by the client.
session_start();

// Retrieve the POSTed data and convert to a PHP array
$ajaxRequest = json_decode(file_get_contents("php://input"), true);
if (null === $ajaxRequest) {
    die("Nothing POSTed!");
}

// ============================================================================
//
// PROCESS THE POSTED ARGUMENTS
//
// ============================================================================

// Do we have a JSON-RPC 2.0 request?
if (!(array_key_exists('jsonrpc', $ajaxRequest) &&
        $ajaxRequest['jsonrpc'] == "2.0")) {

    // Invalid JSON-RPC 2.0 call
    die("Invalid JSON-RPC 2.0 call.");
};

// The session ID must be present. If it is not valid, it should be set to -1.
if (!(array_key_exists('id', $ajaxRequest))) {

    // Session ID missing
    die("Session ID is missing!");

}

// Retrieve the session ID from the client
$clientID = $ajaxRequest['id'];

// Do we have a method with params?
if (!isset($ajaxRequest['method']) && !isset($ajaxRequest['params'])) {

    // Expected 'method' and 'params'
    die("Expected 'method' and 'params'.");
}

// Get the method
$method = $ajaxRequest['method'];

// Method parameters
$params = null;
if (array_key_exists('params', $ajaxRequest)) {
    $params = $ajaxRequest['params'];
}

// Call the method
switch ($method) {

    case "logIn":

        // Make sure the expected parameters exist
        if (!(array_key_exists('username', $params) &&
            array_key_exists('password', $params))) {
            die("Invalid arguments.");
        }

        $json = logIn($params["username"], $params["password"]);
        break;

    case "logOut":

        $json = logOut($clientID);
        break;

    case "isLoggedIn":

        $json = isLoggedIn($clientID);
        break;

    default:

        // Unknown method
        die("Unknown method.");
}

// Return the JSON object
header("Content-Type: application/json", true);
echo $json;

return true;

// ============================================================================
//
// INTERNAL METHODS
//
// ============================================================================

/**
 * Create default (PHP) array with "id", "success", "results" and "message" properties.
 * Methods should initialize their JSON output array with this function, to make
 * sure that there are the expected properties in the returned object (with
 * their default values) and then fill it as needed.
 *
 * The default properties and corresponding values are:
 *
 * "id"     : session id. For most operations, this is received from the client. The
 *            'login' operation (or method) will initialize the (PHP) session and return
 *            the session id to the client. The client will then use this id for all
 *            subsequent operations. Following the JSON RPC 2.0 specifications, the id
 *            received from the client MUST be returned to the client.
 *            The function initializes it to -1 (invalid session id).
 * "success": whether the call was successful (boolean true) or not (false).
 *            Defaults to false.
 * "message": typically an error message to be displayed or parsed by the client
 *            in case "success" is false.
 * "result" : encapsulates the actual result from the call.
 *
 * Before the method functions return, they must call json_encode() on it!
 *
 * @return Array (PHP) with "id" => -1, "result" => "",
 *         "success" => false and "message" => "" properties.
 */
function __initJSONArray()
{
    // Initialize the JSON array with failure
    return (array(
            "id" => -1,
            "result" => "",
            "success" => false,
            "message" => ""));
}

/**
 * This method is used internally to make sure that the session is active
 * and the User is logged in before any action is performed. If this function
 * returns that the session is not active (and the User is therefore not logged
 * in), no method should be executed.
 *
 * @param $client_session_id string|integer Session ID obtained from the client.
 * @return array Result array with keys "can_run" (true|false) and "message".
 *               The message contains a human-friendly explanation of what
 *               went wrong.
 */
function __isSessionActive($client_session_id) {

    // Initialize output
    $result = array("can_run" => true, "message" => "");

    // Check if the ID exists in the session
    if ($client_session_id != session_id()) {

        // The User is not logged in and there is no active session.
        $result["can_run"] = false;
        $result["message"] = "Invalid session id.";

        return $result;
    }

    // Check that the session has a User ID
    if (! array_key_exists('UserID', $_SESSION)) {

        // The User ID was not found in the PHP session.
        $result["can_run"] = false;
        $json["message"] = "The user does not exist.";

        return $result;
    }

    return $result;
}

/**
 * Checks whether a given method can be run by current User's role.
 *
 * This function will not completely validate the session. It just
 * tries to retrieve the current User from the session and compares
 * his role to the one hard-coded in this method.
 *
 * Before the method is called, the __sessionIsActive() method should
 * be called to make sure that a session is active at all!
 *
 * @param $methodName string One of the methods run in this script.
 * @return bool True if the method can be run, false otherwise.
 * @throws \Exception It the role returned for the User is not recognized.
 * @throws \Propel\Runtime\Exception\PropelException
 */
function __isMethodAllowed($methodName) {

    // The logIn method can always be run
    if ($methodName == "logIn") {
        return true;
    }

    // Check it the UserID is stored in the session
    if (array_key_exists('UserID', $_SESSION)) {

        // Try retrieving the user
        $user = UserQuery::create()->findOneById($_SESSION['UserID']);
        if (null === $user) {
            return false;
        }

        // Get User role
        $role = $user->getRole();
        switch ($role) {
            case "user":    $roleInt = 0; break;
            case "manager": $roleInt = 1; break;
            case "admin":   $roleInt = 2; break;
            default: throw new \Exception("Unknown User role!");
        }

        // Define the minimum roles
        $methodRoles = array("addUser" => 3); // TODO: Example!!

        // Compare the role requirements
        if (array_key_exists($methodName, $methodRoles)) {
            return ($methodRoles[$methodName] <= $roleInt);
        } else {
            return true;
        }
    }

    return true;
}

/**
 * Fills the JSON array in case of success.
 *
 * @param $id string|integer Session ID
 * @param $result Object Result from the method call.
 * @param $message string Message returned by the method call.
 * @return Array JSON object.
 */
function __setSuccess($id, $result = "", $message = "")
{
    // Initialize the JSON array
    $json = __initJSONArray();

    // Fill
    $json['id'] = $id;
    $json['success'] = true;
    $json['result'] = $result;
    $json['message'] = $message;

    // Return it
    return $json;
}

/**
 * Fills the JSON array in case of failure.
 *
 * @param $id string|integer Session ID
 * @param $result Object Result from the method call.
 * @param $message string Message returned by the method call.
 * @return Array JSON object.
 */
function __setFailure($id, $result = "", $message = "")
{
    // Initialize the JSON array
    $json = __initJSONArray();

    // Fill
    $json['id'] = $id;
    $json['success'] = false;
    $json['result'] = $result;
    $json['message'] = $message;

    // Return it
    return $json;
}

// ============================================================================
//
// METHOD IMPLEMENTATIONS
//
// ============================================================================

/**
 * Attempts to login the user with name and password received from the client and return
 * the result.
 *
 * @param $username String Name of the User
 * @param $password String Password of the User
 * @return array JSON array.
 */
function logIn($username, $password) {

    // Query the User
    $user = UserQuery::create()->findOneByName($username);
    if (null === $user) {

        // The User does not exist!
        return json_encode(__setFailure(-1, "",
                "The user does not exist."));

    }

    // Prepare the result
    $result = array("success" => null, "role" => null);

    // Try authenticating the user.
    if ($user->logIn($password)) {

        // Destroy previous session
        session_unset();
        session_destroy();

        // Start the new session and store the key in
        // the JSON reply
        session_start();

        // Fill in the result array
        $result["success"] = true;
        $result["role"] = $user->getRole();

        // Successful login
        $json = __setSuccess(session_id(), $result,
                "The user was logged in successfully.");

        // Store the User ID in the PHP session
        $_SESSION['UserID'] = $user->getId();

    } else {

        // Fill in the result array
        $result["success"] = false;
        $result["role"] = null;

        // Fill the JSON array
        $json = __setSuccess(-1, $result,
                "The user could not be logged in.");

    }

    // Return as a JSON string
    return (json_encode($json));
}

function logOut($client_session_id) {

    // Check the session and the User login state
    $result = __isSessionActive($client_session_id);
    if (! $result['can_run']) {

        // Report failure
        $json = __setFailure(-1, false, $result['message']);

    } else {

        // Destroy current session
        session_unset();
        session_destroy();

        // Report success
        $json = __setSuccess(-1, true,
                "The user was logged out successfully.");

    }

    // Return as a JSON string
    return (json_encode($json));
}

/**
 * Checks whether the user with given user name is logged in
 * the result.
 *
 * TODO: This is a toy example to test the communication client-server.
 *
 * For this to succeed, the following must be satisfied:
 *
 *    - the client ID must match current PHP session ID
 *    - the PHP session id must contain the User ID
 *    - $username must match the name of the User stored in the PHP session
 *
 * @param $client_session_id string|integer session id obtained from the client.
 * @return array JSON array.
 * @internal param $client_id : session id obtained from the client.
 * @internal param $username : name of the User to test for log in status.
 */
function isLoggedIn($client_session_id) {

    // Check the session and the User login state
    $result = __isSessionActive($client_session_id);
    if (! $result['can_run']) {

        // Report failure
        $json = __setFailure(-1, false, $result['message']);

    } else {

        if (! __isMethodAllowed("isLoggedIn")) {

            // Report failure
            $json = __setFailure(-1, false,
                    "The user is not allowed to perform this operation.");

        } else {

            // Report success
            $json = __setSuccess($client_session_id, true,
                    "The user is logged in.");

        }
    }

    // Return as a JSON string
    return (json_encode($json));
}
