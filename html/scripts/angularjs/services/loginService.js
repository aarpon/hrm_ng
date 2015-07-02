/**
 * Created by oburri on 02.07.15.
 * This service handles logging the user in and also serves to check the status on the server
 */

hrmapp.service('loginService', function( $http, $q ) {
    var userid = -1;

    //return available functions
    return({
        loginUser: loginUser,
        checkLoginStatus: isLoggedIn,
        getUserId: getUserID,
        logoutUser: logoutUser
    });


    function loginUser( userObj ) {

        var request = $http({
            method: 'POST',
            url: 'ajax/json-rpc-server.php',
            data: {
                id: userid,
                jsonrpc: "2.0",
                method: "logIn",
                params: userObj
            },
            headers: {'Content-Type': 'application/json'}  // set the headers so angular passing info as form data (not request payload)
        });

        return ( request.then( handleSuccess, handleError ) );
    }

    function isLoggedIn() {
        var request = $http({
            method: 'POST',
            url: 'ajax/json-rpc-server.php',
            data: {
                id: userid,
                jsonrpc: "2.0",
                method: "isLoggedIn",
                params: {}
            },
            headers: {'Content-Type': 'application/json'}  // set the headers so angular passing info as form data (not request payload)
        });

        return ( request.then( handleSuccess, handleError ) );
    }

    function getUserID() {
        return userid;
    }


    function logoutUser( ) {
        if (userid != -1) {
            var request = $http({
                method: 'POST',
                url: 'ajax/json-rpc-server.php',
                data: {
                    id: userid,
                    jsonrpc: "2.0",
                    method: "logOut",
                    params: {}
                },
                headers: {'Content-Type': 'application/json'}  // set the headers so angular passing info as form data (not request payload)
            });

            return ( request.then( handleSuccess, handleError ) );
        } else {
            return($q.reject( "You are not logged in"))
        }
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
        if(response.data.success && response.data.result) {
            userid = response.data.id;
        }

        return {result: response.data.result, message: response.data.message}


    }


});