/**
 * Created by oburri on 7/8/15.
 */
hrmapp.config(['$routeProvider',
    function($routeProvider, USER_ROLES) {
        $routeProvider.
            when('/login', {
                templateUrl: 'templates/login-template.html',
                controller: 'loginController'

            }).
            when('/main', {
                templateUrl: 'templates/main-page-template.html',
                controller: 'mainPageController'

            }).
            when('/users', {
                templateUrl: 'templates/manage-users-template.html',
                controller: 'userManagerController'
            }).
            when('/admin', {
                templateUrl: 'templates/hrm-admin-template.html',
                controller: 'hrmAdminController'
            }).
            otherwise({
                redirectTo: '/login'
            });
    }]);
