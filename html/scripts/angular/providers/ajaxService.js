/**
 * Created by oburri on 02.07.15.
 */
hrmapp.service('ajaxService', function( $http, $q ) {

    //return available functions
    return({
        sendRequest: sendRequest
    });

    function sendRequest(method, parameters) {

        var request = $http({
            method: 'POST',
            url: 'ajax/json-rpc-server.php',
            headers: {'Content-Type': 'application/json'},
            data: {
                method: method,
                params: parameters
            }
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
            return {result: response.data, message: response.data.message}
        }
        return( $q.reject( response.data.message ) );




    }

});