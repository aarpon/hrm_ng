/**
 * Created by oburri on 02.07.15.
 */
hrmapp.service('logService', function($rootScope) {
    var logData = [];

    // This expects an array that contains two elements
    // the log level, and the message
    var addLog = function(logObj) {
        logData.push(logObj);
        $rootScope.$broadcast('event:log-change');
    };

    var delLog = function(index) {
        if (index != undefined) {
            logData.pop();
        } else {
            logData.splice(index,1);
        }
        $rootScope.$broadcast('event:log-change');


    };

    var getLogs = function() {
        return logData;
    };

    // This service returns the above functions so that whatever controller needs the log can use it
    return {
        addLog: addLog,
        delLog: delLog,
        getLogs: getLogs
    };

});