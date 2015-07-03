/**
 * Created by oburri on 02.07.15.
 */
hrmapp.service('uiLogService', function($rootScope, LOG_EVENTS) {
    var logData = [];

    // This expects an array that contains two elements
    // the log level, and the message
    this.addLog = function(level, message) {
        logData.push({level: level, message: message});
        $rootScope.$broadcast('event:uilog-change');
    };

    this.delLog = function(index) {
        if (index == undefined) {
            logData.pop();
        } else {
            logData.splice(index,1);
        }
        $rootScope.$broadcast('event:uilog-change');
    };

    this.getLogs = function() {
        return logData;
    };

});