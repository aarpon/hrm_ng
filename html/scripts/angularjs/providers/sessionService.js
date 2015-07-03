/**
 * Created by oburri on 03.07.15.
 */
hrmapp.service('sessionService', function() {


    return ({
        create: create,
        destroy: destroy
    });

    function create(sessionId, userId, userRole) {
        this.id = sessionId;
        this.userId = userId;
        this.userRole = userRole;
    };

    function destroy() {
        this.id = -1;
        this.userId = null;
        this.userRole = null;
    };
})