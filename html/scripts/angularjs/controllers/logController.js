/**
 * This is a simple controller to manage the logs on our pages
 * it listens to the log service and on change, displays the log
 */
hrmapp.controller('logController', function($scope, $rootScope, logService) {
    $scope.log =[];

    $scope.log = logService.getLogs();

    $rootScope.$on('event:log-change', function() {
        $scope.log = logService.getLogs();

    });
    $scope.closeLog = function (index) {
        logService.delLog(index);
    };
});