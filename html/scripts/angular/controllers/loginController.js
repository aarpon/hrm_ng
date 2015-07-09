/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('loginController', function($scope, $location, $rootScope, $mdDialog, authService, AUTH_EVENTS, LOG_EVENTS, toastService) {
    $scope.credentials = {
        username: 'TestUser',
        password: 'TestPassword'
    }
    $scope.loginUser = function(credentials) {
        authService.loginUser(credentials).then(function () {
            toastService.showMessage('Login Successful');
            $location.path('/main');

        }, function (errorMessage) {
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            toastService.showMessage('Login Failed: '+errorMessage);

        });
    };

    $scope.showNewUserForm = function(ev) {
        $mdDialog.show({
            controller: newUserFormController,
            templateUrl: 'templates/new-user-template.html',
            parent: angular.element(document.body),
            targetEvent: ev
        })
            .then(function(newuserinfo) {
                $scope.newuser = newuserinfo;
                // Submit here to the server
            }, function() {
                toastService.showMessage('New User Form Cancelled');
            });
    };
});

function newUserFormController($scope, $mdDialog, ajaxService) {

    $scope.cancel = function() {
        $mdDialog.cancel();
    };
    $scope.submitNewUser = function(userdata) {
        $mdDialog.hide(userdata);
    };
}
