/**
 * Created by oburri on 03.07.15.
 * This ensures that ALL requests to the server contain the needed data for a clean JSON request
 */
hrmapp.factory('hrmInterceptor', ['hrmSession', function(hrmSession) {
    var hrmInterceptor = {
        request: function(request) {
            if (request.method == 'POST') {
                request.data.id = hrmSession.sessionId;
                request.data.jsonrpc=  "2.0";

                // Here we make sure that the data.params and data.method are defined
                if (!'params' in request.data) request.data.params = 'empty';
                if (!'method' in request.data) request.data.method = 'empty';
            }
            return request;
        }
    };
    return hrmInterceptor;
}]);

hrmapp.config(['$httpProvider', function($httpProvider) {
    $httpProvider.interceptors.push('hrmInterceptor');
}]);

