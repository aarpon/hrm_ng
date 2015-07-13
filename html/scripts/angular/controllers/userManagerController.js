/**
 * Created by oburri on 7/13/15.
 */
hrmapp.controller('userManagerController', function ($scope, ajaxService, toastService) {

    $scope.userData = [
        {
            username: 'bob',
            email: 'bob@bobland.com',
            role: 'user',
            is_active: true,
            remark: ''
        },
        {
            username: 'john',
            email: 'john@killbob.com',
            role: 'user',
            is_active: false,
            remark: ''
        },
        {
            username: 'mike',
            email: 'mike@lovejohn.com',
            role: 'user',
            is_active: true,
            remark: ''
        },
        {
            username: 'gandalf',
            email: 'gandalf@middleearth.com',
            role: 'admin',
            is_active: true,
            remark: 'You shall not pass'
        },
        {
            username: 'captain',
            email: 'captain@bobland.com',
            role: 'manager',
            is_active: false,
            remark: 'I own you'
        }
];


     var getUsers = function () {
        ajaxService.sendRequest('getUsers').then(function(data) {
            toastService.showMessage(data.message);

            $scope.userData = data.result.userData;
            toastService.showMessage('User Retrieval Successful');
        }, function(message) {
            toastService.showMessage('Error Getting Users: '+message);

        })
    };

    //$scope.users = getUsers();


    $scope.toggleUser = function(userIndex) {
        ajaxService.sendRequest('toggleUser', {username:$scope.userData[userIndex].username, is_active: !$scope.userData[userIndex].is_active}).then(function(data) {
            var txt = (data.result.is_active ? "active" : "inactive");
            $scope.userData[userIndex].is_active = data.result.is_active;
            toastService.showMessage('User ' + $scope.users[userIndex].username + ' is now '+ txt);
        })
    };

    $scope.deleteUser = function(userIndex) {
        ajaxService.sendRequest('deleteUser', {username:$scope.userData[userIndex].username}).then(function(data) {
            var deletedUser = $scope.userData.splice(userIndex,1);
            toastService.showMessage('User ' + deletedUser + ' was deleted');
        })
    };


});
