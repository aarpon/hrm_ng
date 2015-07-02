/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('loginController', function($scope, logService, loginService) {

    $scope.isLoggedIn = false;

    logService.addLog({type: 'success', message: 'test'});

    $scope.loginUser = function() {
        loginService.loginUser($scope.user)
            .then(
            manageSuccessfulLogin, function (errorMessage) {
                logService.addLog(['error', errorMessage]);
            }
        )
    };

    $scope.logoutUser = function() {
        loginService.logoutUser()
            .then(
            manageSuccessfulLogout, function (errorMessage) {
                logService.addLog(['error', errorMessage]);
            }
        )
    };


    function manageSuccessfulLogout(data) {

        $scope.isLoggedIn = !data.result;

        logService.addLog({type: 'success', message: 'Logout Result was: '+ data.result + ' Message: '+data.message});
        logService.addLog({type: 'info',  message: 'userID from loginService: '+loginService.getUserId()});
    };

    function manageSuccessfulLogin(data) {

        $scope.isLoggedIn = !data.result;

        logService.addLog({type: 'success', message: 'Login Result was: '+ data.result.toString() + ' Message: '+data.message});
        logService.addLog({type: 'info',  message: 'userID from loginService: '+loginService.getUserId()});
    };
});