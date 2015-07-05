/**
 * Created by oburri on 02.07.15.
 * This service handles logging the user in and out
 */

hrmapp.factory('authService', function( ajaxService, hrmSession ) {

    var authService = {};

    authService.loginUser = function( user ) {

        return ajaxService.sendRequest('logIn', user)
            .then(function (res) {
                if (res.data.result.success) {
                    hrmSession.create(user.name, res.data.id, res.data.result.role);
                    return user.name; // give the user name back.
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