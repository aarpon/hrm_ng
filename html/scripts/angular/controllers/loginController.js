/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('loginController', function($scope, $location, $rootScope, $mdDialog, authService, AUTH_EVENTS, LOG_EVENTS, $mdToast) {

    $scope.loginUser = function(credentials) {
        authService.loginUser(credentials).then(function (user) {
            $scope.setCurrentUser(user);
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);

            $mdToast.show(
                $mdToast.simple()
                    .content('Successful Login')
                    .position("top right")
                    .hideDelay(5000)
            );
            $location.path('main');

        }, function (errorMessage) {
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            $mdToast.show(
                $mdToast.simple()
                    .content('Failed:'+errorMessage)
                    .position("top right")
                    .hideDelay(2000)
            );
        });
    };

    $scope.showNewUserForm = function(ev) {
        $mdDialog.show({
            controller: newUserFormController,
            templateUrl: 'templates/new-user.html',
            parent: angular.element(document.body),
            targetEvent: ev
        })
            .then(function(newuserinfo) {
                $scope.newuser = newuserinfo;
                // Submit here to the server
            }, function() {
                $mdToast.show(
                    $mdToast.simple()
                        .content('Cancelled')
                        .position("top right")
                        .hideDelay(2000)
                );
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
