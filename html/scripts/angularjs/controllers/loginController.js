/**
 * Created by oburri on 02.07.15.
 */

hrmapp.controller('loginController', function($scope, $localStorage, logService, loginService) {

    $scope.isLoggedIn = false;

    $scope.loginUser = function() {
        loginService.loginUser($scope.user)
            .then(
            manageSuccessfulLogin(data), function (errorMessage) {
                logService.addLog(['error', errorMessage]);
            }
        )
    };

    $scope.logoutUser = function() {

    };


    function manageSuccessfulLogin(data) {
        $scope.isLoggedIn = true;

        logService.addLog('success', 'Result was: '+ data.result + ' Message: '+data.message);
        logService.addLog('info', 'userID from loginService: '+loginService);
    };
});