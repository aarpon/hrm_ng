hrmapp.service('toastService', function($mdToast) {

    this.showMessage = function(message, timeout) {
        timeout = timeout || 2000;
        $mdToast.show(
            $mdToast.simple()
                .content(message)
                .position("top right")
                .hideDelay(timeout)
        );
    }
});