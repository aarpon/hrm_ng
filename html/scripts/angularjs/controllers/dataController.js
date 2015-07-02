/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('dataController', function( $scope, logService, ajaxService, loginService, $rootScope) {

    /*
    This wants to grab data for the user, let's make a simple function
     */

    $scope.isLoggedIn = true;
    $scope.theData = [];
    $scope.request = [];
    $scope.request.method = 'isLoggedIn';



    $scope.getSensitiveData = function() {
        ajaxService.sendRequest($scope.request.method)
            .then(
            manageSuccessfulRequest, function (errorMessage) {
                logService.addLog(['error', errorMessage]);
            }
        )
    };

    function manageSuccessfulRequest(data) {
        if (data.success) {
            $scope.theData = data.result;
        }
        logService.addLog({type: 'success', message: 'Result was: '+ data.result + ' Message: '+data.message});
    }

});