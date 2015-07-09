/**
 * Created by oburri on 02.07.15.
 * This service handles logging the user in and out
 */

hrmapp.factory('authService', function( $rootScope, ajaxService, hrmSession, AUTH_EVENTS ) {

    var authService = {};

    authService.loginUser = function( user ) {

        return ajaxService.sendRequest('logIn', user)
            .then(function (res) {
                if (res.result.success) {
                    hrmSession.create(user.username, res.result.id, res.result.result.role);
                    $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);

                } else{
                    $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                    return ($q.reject(res.message))
                }

            });
    };

    authService.logoutUser = function() {

        return ajaxService.sendRequest('logOut')
            .then(function (res) {
                if (res.result.success) {
                    hrmSession.destroy();
                    $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);

                } else{
                    $rootScope.$broadcast(AUTH_EVENTS.logoutFailed);
                    return ($q.reject(res.message))
                }

            });
    };


    authService.isAuthenticated = function() {
        return hrmSession.sessionId != -1;
    };

    authService.isAuthorized = function(authorizedRoles) {
        if (!angular.asArray(authorizedRoles)) {
            authorizedRoles = [authorizedRoles];
        }
        return (authService.isAuthenticated() && authorizedRoles.indexOf(hrmSession.userRole) !== -1);
    };

    return authService;

});