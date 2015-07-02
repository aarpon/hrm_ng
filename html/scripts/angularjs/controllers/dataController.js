/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('dataController', function( $scope, logService, ajaxService, loginService, $rootScope) {

    /*
    This wants to grab data for the user, let's make a simple function
     */

    $scope.isLoggedIn = false;
    $scope.theData = [];
    $scope.request = [];
    $scope.request.method = 'isLoggedIn';

    $rootScope.$on('event:user-change', function() {
        $scope.isLoggedIn = loginService.isLoggedIn();
    });

    $scope.getSensitiveData = function() {
        ajaxService.sendRequest($scope.request.method)
            .then(
            manageSuccessfulRequest, function (errorMessage) {
                logService.addLog(['error', errorMessage]);
            }
        )
    };

    function manageSuccessfulRequest(data) {
        $scope.theData = data.result;

        logService.addLog({type: 'success', message: 'Result was: '+ data.result + ' Message: '+data.message});
    }

});