/**
 * Created by oburri on 03.07.15.
 * This ensures that ALL requests to the server contain the needed data for a clean JSON request
 */
hrmapp.factory('hrmInjector', ['SessionService', function(SessionService) {
    var hrmInjector = {
        request: function(request) {
            request.headers = {'Content-Type': 'application/json'} ; // set the headers so angular passing info as form data (not request payload)
            request.data['id'] = SessionService.id;
            request.data['jsonrpc'] = "2.0";
            return request;
        }
    };
    return hrmInjector;
}]);


hrmapp.config(['$httpProvider', function($httpProvider) {
    $httpProvider.interceptors.push('hrmInjector');
}]);

