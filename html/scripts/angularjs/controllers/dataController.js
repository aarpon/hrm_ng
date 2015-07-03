/**
 * Created by oburri on 02.07.15.
 */
hrmapp.controller('dataController', function( $scope, uiLogService, ajaxService) {

    /*
    This wants to grab data for the user, let's make a simple function
     */

    $scope.isLoggedIn = false;
    $scope.theData = [];
    $scope.request = [];
    $scope.request.method = 'isLoggedIn';



    $scope.getSensitiveData = function() {
        ajaxService.sendRequest($scope.request.method)
            .then(
            manageSuccessfulRequest, function (errorMessage) {
                uiLogService.addLog(['error', errorMessage]);
            }
        )
    };

    function manageSuccessfulRequest(data) {
        if (data.success) {
            $scope.theData = data.result;
        }
        uiLogService.addLog({type: 'success', message: 'Result was: '+ data.result + ' Message: '+data.message});
    }

});