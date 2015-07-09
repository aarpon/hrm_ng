/**
 * Created by oburri on 02.07.15.
 * This service handles logging the user in and out
 */

hrmapp.factory('authService', function( $rootScope, ajaxService, hrmSession, AUTH_EVENTS ) {

    var authService = {};

    authService.loginUser = function( user ) {

        return ajaxService.sendRequest('logIn', user)
            .then(function (res) {
                    hrmSession.create(user.username, res.id, res.result.role);
                    $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);

            }, function (errorMessage) {
                $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                return (errorMessage)
            });
    };

    authService.logoutUser = function() {

        return ajaxService.sendRequest('logOut')
            .then(function (res) {
                hrmSession.destroy();
                $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
            }, function(errorMessage) {
                    $rootScope.$broadcast(AUTH_EVENTS.logoutFailed);
                    return (errorMessage)

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