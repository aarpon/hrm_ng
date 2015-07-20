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
use hrm\RPC\JSONRPCServer;

require_once dirname(__FILE__) . '/../../src/bootstrap.php';

// Retrieve the POSTed data and convert to a PHP array
$ajaxRequest = json_decode(file_get_contents("php://input"), true);
if (null === $ajaxRequest) {
    die("Nothing POSTed!");
}

// Instantiate the JSONRPCServer object and validate the request
$server = new JSONRPCServer($ajaxRequest);

// If the request is valid, execute the requested method
if ($server->isRequestValid())
{
    // Execute the request
    $server->executeRequest();
}

// Return the HTTP/1.1 response
header("HTTP/1.1 " . $server->getResponseStatus());
header("Content-type: " . $server->getResponseContentType(), true);
header("Content-length: " . $server->getResponseContentLength());
echo $server->getResponseBody();

return true;
