/**
 * Created by oburri on 03.07.15.
 */

hrmapp.controller('mainController', function ($scope, $rootScope, $location, AUTH_EVENTS, hrmSession, authService, toastService) {

    $scope.currentUser = null;
    $scope.currentRole = null;

    $scope.logout = function () {
        authService.logoutUser().then(function () {
            toastService.showMessage('Logout Sucessful');

            $location.path('/login');

        }, function (errorMessage) {
            toastService.showMessage('Logout Failed: '+errorMessage);
        });
    };

    $rootScope.$on(AUTH_EVENTS.loginSuccess, function() {
        $scope.currentUser = hrmSession.userName;
        $scope.currentRole = hrmSession.userRole;

    });

    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() {
        $scope.currentUser = null;
        $scope.currentRole = null;

    });
});