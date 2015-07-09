/**
 * Useful constants for HRM
 */
hrmapp.constant('AUTH_EVENTS', {
    loginSuccess: 'auth-login-success',
    loginFailed: 'auth-login-failed',
    logoutSuccess: 'auth-logout-success',
    logoutFailed: 'auth-logout-failed',
    sessionTimeout: 'auth-session-timeout',
    notAuthenticated: 'auth-not-authenticated',
    notAuthorized: 'auth-not-authorized'
});

hrmapp.constant('USER_ROLES', {
    all: '*',
    admin: 'admin',
    user: 'user',
    manager: 'manager'
});

hrmapp.constant('LOG_EVENTS', {
    log: 'log-log',
    warn: 'log-warn',
    info: 'log-info',
    err: 'log-error',
    debug: 'log-debug'
});

