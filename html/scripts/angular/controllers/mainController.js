/**
 * Created by oburri on 03.07.15.
 */

hrmapp.controller('mainController', function ($scope, USER_ROLES, authService) {

    /*
     These variables and methods are all assigned to the $scope
     and thus should NOT be accessed by controllers, only templates!
      */

    $scope.currentUser = null;
    $scope.userRoles = USER_ROLES;
    $scope.isAuthorized = authService.isAuthorized;

    $scope.setCurrentUser = function (user) {
        $scope.currentUser = user;
    };
});