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
use hrm\Base\UserQuery;

require_once dirname(__FILE__) . '/../../src/bootstrap.php';

// This is needed when checking the (session) id submitted by the client.
session_start();

// Retrieve the POSTed data and convert to a PHP array
$ajaxRequest = json_decode(file_get_contents("php://input"), true);
if (null === $ajaxRequest) {
    die("Nothing POSTed!");
}

// TODO: Actions will need to be filtered (i.e. allowed) depending on the
// TODO: login status and role of the User.

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

switch ($method) {

    case "login":

        // Make sure the expected parameters exist
        if (!(array_key_exists('username', $params) &&
            array_key_exists('password', $params))) {
            die("Invalid arguments.");
        }

        $json = login($params["username"], $params["password"]);
        break;

    case "isLoggedIn":

        // Make sure the expected parameters exist
        if (!(array_key_exists('id', $params) &&
                array_key_exists('username', $params))) {
            die("Invalid arguments.");
        }

        $json = isLoggedIn($params["id"], $params["username"]);
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
// METHOD IMPLEMENTATIONS
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
function initJSONArray()
{
    // Initialize the JSON array with failure
    return (array(
            "id" => -1,
            "result" => "",
            "success" => false,
            "message" => ""));
}

/**
 * Attempts to login the user with name and password received from the client and return
 * the result.
 *
 * @param $username String Name of the User
 * @param $password String Password of the User
 * @return array JSON array.
 */
function login($username, $password) {

    // Initialize output JSON array
    $json = initJSONArray();

    // Query the User
    $user = UserQuery::create()->findOneByName($username);
    if (null === $user) {

        // The User does not exist!
        $json['success'] = false;
        $json['message'] = "The user does not exist.";
        return $json;

    }

    // Try authenticating the user.
    if ($user->logIn($password)) {

        // Start the session and store the key in
        // the JSON reply
        session_start();

        // Fill the json array
        $json["id"] = session_id();
        $json["result"] = true;
        $json["success"] = true;

        // Store the User ID in the PHP session
        $SESSION['UserID'] = $user->getId();

    } else {

        // Fill the json array
        $json["result"] = false;
        $json["success"] = true;
        $json["message"] = "The user could not be logged in.";
    }

    // Return as a JSON string
    return (json_encode($json));
}

/**
 * Checks whether the user with given user name is logged in
 * the result.
 *
 * For this to succeed, the following must be satisfied:
 *
 *    - the client ID must match current PHP session ID
 *    - the PHP session id must contain the User ID
 *    - $username must match the name of the User stored in the PHP session
 *
 * @param $client_id: session id obtained from the client.
 * @param $username: name of the User to test for log in status.
 * @return array JSON array.
 */
function isLoggedIn($client_id, $username) {

    // Initialize output JSON array
    $json = initJSONArray();

    // Start the session and check that the ID exists in the session
    start_session();
    if ($client_id != session_id()) {

        // The User is not logged in and there is no active session.
        $json["result"] = false;
        $json["message"] = "Invalid session id.";

        return $json;
    }

    // Retrieve the requested User
    $user = UserQuery::create()->findOneByName($username);
    if (null === $user) {

        // The User was not found.
        $json["result"] = false;
        $json["message"] = "The user does not exist.";

        return $json;
    }

    // Compare the ID
    if (!(array_key_exists('UserID', $_SESSION)) &&
            $_SESSION['UserID'] == $user->getId()) {

        // The User was not found in the session.
        $json["result"] = false;
        $json["message"] = "The user does not exist in the session.";

        return $json;
    }

    // Get the User login status
    $result = $user->isLoggedIn();
    $json['id'] = session_id(); // This is the same as the passed $client_id
    $json["result"] = $result;
    $json["success"] = true;
    $json["message"] = "";

    // Return as a JSON string
    return (json_encode($json));
}
