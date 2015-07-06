/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('loginController', function($scope, $rootScope, $mdDialog, authService, AUTH_EVENTS, LOG_EVENTS, uiLogService) {

    $scope.loginUser = function(credentials) {
        authService.loginUser(credentials).then(function (user) {
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
            $scope.setCurrentUser(user);
        }, function (errorMessage) {
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            uiLogService.addLog(LOG_EVENTS.err, errorMessage)
        });
    };

    $scope.showNewUserForm = function(ev) {
        $mdDialog.show({
            controller: newUserFormController,
            templateUrl: 'templates/new-user.html',
            parent: angular.element(document.body),
            targetEvent: ev,
        })
            .then(function(newuserinfo) {
                $scope.newuser = newuserinfo;
            }, function() {
                $scope.alert = newuserinfo;
            });
    };
});
function newUserFormController($scope, $mdDialog) {
    $scope.hide = function() {
        $mdDialog.hide();
    };
    $scope.cancel = function() {
        $mdDialog.cancel();
    };
    $scope.answer = function(answer) {
        $mdDialog.hide(answer);
    };
}
