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
require_once dirname(__FILE__) . '/../../src/bootstrap.php';

// This is needed when checking the (session) id submitted by the client.
session_start();

// Retrieve the POSTed data
$ajaxRequest = json_decode(file_get_contents("php://input"));
if (null === $ajaxRequest) {
    die("Nothing POSTed!");
}

// TODO: At this stage we filter the actions that are allowed depending on
// TODO: the login status and role of the User.

// If the user is not logged on, we return without doing anything.
//if (!isset($_SESSION['user']) || !$_SESSION['user']->isLoggedIn()) {
//    return;
//}

// ============================================================================
//
// PROCESS THE POSTED ARGUMENTS
//
// ============================================================================

// TODO: Check that we have a valid request

// Do we have a JSON-RPC 2.0 request? TODO: check id!
if (!(isset($ajaxRequest['id']) &&
    isset($ajaxRequest['jsonrpc']) && $ajaxRequest['jsonrpc'] == "2.0")) {

    // Invalid JSON-RPC 2.0 call
    die("Invalid JSON-RPC 2.0 call.");
};

// Start the session with passed key
$session_id = $_POST['id'];
session_start($session_id);

// Do we have a method with params?
if (!isset($ajaxRequest['method']) && !isset($ajaxRequest['params'])) {

    // Expected 'method' and 'params'
    die("Expected 'method' and 'params'.");
}

// Get the method
$method = $ajaxRequest['method'];

// Method parameters
$params = null;
if (isset($ajaxRequest['params'])) {
    $params = $ajaxRequest['params'];
}

switch ($method) {

    case "login":

        $json = login($params["username"], $params["password"]);
        break;

    case "isLoggedIn":

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
 * Create default (PHP) array with "id", "success" and "message" properties.
 * Methods should initialize their JSON output array with this function, to make
 * sure that there are the expected properties in the returned object (with
 * their default values) and then fill it as needed.
 *
 * The default properties and corresponding values are:
 *
 * "id"     : id received from the client. Following the JSON RPC 2.0
 *            specifications the received id must be returned to the client.
 *            The function initializes it to -1 (invalid id).
 * "success": whether the call was successful ("true") or not ("false").
 *            Defaults to "true".
 * "message": typically an error message to be displayed or parsed by the client
 *            in case "success" is "false".
 * "result" : encapsulates the actual result from the call.
 *
 * Before the method functions return, they must call json_encode() on it!
 *
 * Note: PHP booleans true and false MUST be mapped to the corresponding strings
 * "true" and "false".
 *
 * @return Array (PHP) with "id" => -1, "result" => "",
 *         "success" => "true" and "message" => "" properties.
 */
function initJSONArray()
{
    // TODO: Validate id

    // Initialize the JSON array with success
    return (array(
            "id" => -1,
            "result" => "",
            "success" => true,
            "message" => ""));
}

/**
 * Attempts to login the user with name and password received from the client and return
 * the result.
 *
 * @param $username String Name of the User
 * @param $password String Password of the User
 * @return bool True if login was successful, false otherwise.
 */
function login($username, $password) {

    // Initialize output JSON array
    $json = initJSONArray();

    // Initialize a new User
    $user = new User();

    // TODO: Actually login the user.
    $result = false;
    if ($user->login()) {
        $result = true;

        // TODO: Start the session and store the key
        // TODO: in the JSON reply
    }
    $json["result"] = $result;

    // Return as a JSON string
    return (json_encode($json));
}

/**
 * Checks whether the user with given user name is logged in
 * the result.
 *
 * @param $client_id: session id obtained from the client.
 * @param $username: name of the user to test for log in status.
 * @return bool True if the user is logged in, false otherwise.
 */
function isLoggedIn($client_id, $username) {

    // Initialize output JSON array
    $json = initJSONArray();

    // TODO: Check that the ID exists in the session

    // Initialize a new User
    $user = UserQuery::create()->findByName($username);
    if ($user->count() == 0) {
        // TODO: Send back failure via JSON
    }

    // Get the User login status
    $result = $user[0]->isLoggedIn();
    $json["result"] = $result;

    // Return as a JSON string
    return (json_encode($json));
}
