<?php

/*

Server implementing the JSON-RPC (version 2.0) protocol.

This is an example Javascript code to interface with json-rpc-server.php:

01:    <script type="text/javascript">
02:        $(document).ready($('#button').click(function() {
03:            JSONRPCRequest({
04:                method : 'isLoggedIn',
05:                params : { userName : 'name'}
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

// Check that we have a valid request
if (!isset($_POST)) {
    die("Nothing POSTed!");
}

// Do we have a JSON-RPC 2.0 request? TODO: check id!
if (!(isset($_POST['id']) &&
    isset($_POST['jsonrpc']) && $_POST['jsonrpc'] == "2.0")) {

    // Invalid JSON-RPC 2.0 call
    die("Invalid JSON-RPC 2.0 call.");
};

// Do we have a method with params?
if (!isset($_POST['method']) && !isset($_POST['params'])) {

    // Expected 'method' and 'params'
    die("Expected 'method' and 'params'.");
}

// Get the method
$method = $_POST['method'];

// Method parameters
$params = null;
if (isset($_POST['params'])) {
    $params = $_POST['params'];
}

// Call the requested method and collect the JSON-encoded response
switch ($method) {

    case "login":

        $json = login($params);
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
 * @param $client_id: id received from the client.
 * @return Array (PHP) with "id" => <passed id>, "result" => "",
 *         "success" => "true" and "message" => "" properties.
 */
function initJSONArray($client_id)
{
    // TODO: Validate id

    // Initialize the JSON array with success
    return (array(
        "id" => $client_id,
        "result" => "",
        "success" => true,
        "message" => ""));
}

/**
 * Attempts to login the user with name and password received from the client and return
 * the result.
 *
 * @param $client_id: id obtained from the client.
 * @return bool True if login was successful, false otherwise.
 */
function login($client_id) {

    // Initialize output JSON array
    $json = initJSONArray($client_id);

    // Initialize a new User
    $user = new User();

    // TODO: Actually login the user.
    $result = false;
    if ($user->login()) {
        $result = true;
    }
    $json["result"] = $result;

    // Return as a JSON string
    return (json_encode($json));
}
