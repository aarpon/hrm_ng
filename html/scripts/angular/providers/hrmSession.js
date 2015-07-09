/**
 * This service stores the local user information that we need in order to display things on the client.
 */

hrmapp.service('hrmSession', function() {
    this.sessionId = -1;
    this.userName = null;
    this.userRole = null;

    this.create = function (userName, sessionId, userRole) {
        this.userName = userName;
        this.sessionId = sessionId;
        this.userRole = userRole;
    };

    this.destroy = function () {
        this.userName = null;
        this.sessionId = -1;
        this.userRole = null;
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