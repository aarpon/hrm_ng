/**
 * Created by oburri on 02.07.15.
 * This service handles logging the user in and out
 */

hrmapp.factory('authService', function( ajaxService, hrmSession ) {

    var authService = {};

    authService.loginUser = function( user ) {

        return ajaxService.sendRequest('logIn', user)
            .then(function (res) {
                if (res.result.success) {
                    hrmSession.create(user.username, res.result.id, res.result.result.role);
                    return user.username; // give the user name back.
                } else{
                    return ($q.reject(res.message))
                }

            });
    };


    authService.isAuthenticated = function() {
        return hrmSession.sessionID;
    };

    authService.isAuthorized = function(authorizedRoles) {
        if (!angular.asArray(authorizedRoles)) {
            authorizedRoles = [authorizedRoles];
        }
        return (authService.isAuthenticated() && authorizedRoles.indexOf(hrmSession.userRole) !== -1);
    };

    return authService;

});