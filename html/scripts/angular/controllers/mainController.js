/**
 * Created by oburri on 03.07.15.
 */

hrmapp.controller('mainController', function ($scope, $rootScope, $location, AUTH_EVENTS, hrmSession, USER_ROLES, authService, toastService) {

    $scope.currentUser = null;
    $scope.currentRole = null;

    $scope.switchView = function (path) {
        $location.path(path);
    }

    $scope.logout = function () {
        authService.logoutUser().then(function () {
            toastService.showMessage('Logout Successful');
            $location.path('/login');

        }, function (errorMessage) {
            toastService.showMessage('Logout Failed: '+errorMessage);
        });
    };

    $scope.isLoggedIn = function() {
        return authService.isAuthenticated();
    }

    $scope.isAdmin = function() {
        return authService.isAuthorized(USER_ROLES.user);
    }
    $scope.isManager = function() {
        return authService.isAuthorized(USER_ROLES.user);
    }



    $rootScope.$on(AUTH_EVENTS.loginSuccess, function() {
        $scope.currentUser = hrmSession.userName;
        $scope.currentRole = hrmSession.userRole;
        loadManagementOptions();

    });

    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() {
        $scope.currentUser = null;
        $scope.currentRole = null;
        $scope.managementOptions = [];

    });

    // Management Menu Options depending on user rights
    $scope.managementOptions = [];

    var loadManagementOptions = function() {
        if( $scope.isManager() ) {
            $scope.managementOptions.push({name: 'Manage Users', view:'/users'});
        }
        if( $scope.isAdmin() ) {
            $scope.managementOptions.push({name: 'HRM Administration', view:'/admin'});
        }
    };


});