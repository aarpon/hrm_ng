/**
 * Created by oburri on 7/9/15.
 */
hrmapp.controller('mainPageController', function ($scope, $rootScope, authService) {
    $scope.logout = function () {
        authService.logoutUser().then;
    }
});