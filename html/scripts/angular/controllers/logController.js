/**
 * This is a simple controller to manage the logs on our pages
 * it listens to the log service and on change, displays the log
 */
hrmapp.controller('logController', function($scope, $rootScope, uiLogService) {
    $scope.logs =[];

    $scope.logs = uiLogService.getLogs();

    $rootScope.$on('event:log-change', function() {
        $scope.logs = uiLogService.getLogs();

    });
    $scope.closeLog = function (index) {
        uiLogService.delLog(index);
    };
});