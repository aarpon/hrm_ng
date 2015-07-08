/**
 * This service stores the local user information that we need in order to display things on the client.
 */

hrmapp.service('hrmSession', function() {
    this.sessionId = -1;
    this.userName = "";
    this.userRole = "";

    this.create = function (userName, sessionId, userRole) {
        this.userName = userName;
        this.sessionId = sessionId;
        this.userRole = userRole;
    };

    this.destroy = function () {
        this.userName = userName;
        this.sessionId = sessionId;
        this.userRole = userRole;
    };

    this.getSessionId = function() {
        return this.sessionId;
    }
    this.getUserName = function() {
        return this.userName;
    }
    this.getRole = function() {
        return this.userRole;
    }
});