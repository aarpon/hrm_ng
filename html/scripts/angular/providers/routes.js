/**
 * Created by oburri on 7/8/15.
 */
hrmapp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/login', {
                templateUrl: 'templates/login-template.html',
                controller: 'loginController'
            }).
            when('/main', {
                templateUrl: 'templates/main-page-template.html',
                controller: 'mainPageController'
            }).
            when('/manage', {
                templateUrl: 'templates/manage-users-template.html',
                controller: 'userManagerController'
            }).
            otherwise({
                redirectTo: '/login'
            });
    }]);