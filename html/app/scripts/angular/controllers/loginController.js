/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('loginController', function($scope, $rootScope, authService, AUTH_EVENTS, LOG_EVENTS, uiLogService) {
    $scope.user = {
        username: '',
        password: ''
    };

    $scope.loginUser = function(credentials) {
        authService.loginUser(credentials).then(function (user) {
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
            $scope.setCurrentUser(user);
        }, function (errorMessage) {
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            uiLogService.addLog(LOG_EVENTS.err, errorMessage)
        });
    };
});