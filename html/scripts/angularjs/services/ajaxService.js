/**
 * Created by oburri on 02.07.15.
 */
hrmapp.service('ajaxService', function( $http, $q, loginService ) {

    //return available functions
    return({
        sendRequest: sendRequest
    });

    function sendRequest(method, parameters) {
        var userId = loginService.getUserID();

        var request = $http({
            method: 'POST',
            url: 'ajax/json-rpc-server.php',
            data: {
                id: userId,
                jsonrpc: "2.0",
                method: method,
                params: parameters
            },
            headers: {'Content-Type': 'application/json'}  // set the headers so angular passing info as form data (not request payload)
        });

        return ( request.then( handleSuccess, handleError ) );

    }

    /*
     PRIVATE METHODS TO CONVERT THE RESPONSES
     */

    function handleError( response ) {
        if (
            ! angular.isObject( response.data ) ||
            ! response.data.message
        ) {

            return ( $q.reject("An unknown error occurred.") );
        }
        // Otherwise, use expected error message.
        return( $q.reject( response.data.message ) );

    }

    function handleSuccess( response ) {
        if(response.data.success) {
            return {result: response.data.result, message: response.data.message}
        }


    }

});