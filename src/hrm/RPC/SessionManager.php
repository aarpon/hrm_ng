<?php

namespace hrm\RPC;

/**
 * Class SessionManager A SessionManager class to be bundled with JSONRPCServer.
 * @package hrm\hrm\RPC
 */
class SessionManager
{
    /**
     * @var string Last (error) message.
     */
    private $lastMessage = "";

    /**
     * Constructor
     * @param string $sessionId. Session ID to (re)use.
     */
    public function __construct()
    {
        // Start the session
        session_start();

    }

    /**
     * Start a new session
     */
    public function restart()
    {
        // Destroy current session
        session_unset();
        session_destroy();

        // Start a new one
        session_start();
    }

    /**
     * Destroy the session.
     */
    public function destroy()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Set a key-value pair in the Session
     * @param $key string Key
     * @param $val Any Value
     */
    public function set($key, $val)
    {
        $_SESSION[$key] = $val;
    }

    /**
     * Get the value for requested key or null if the key does not exist.§
     * @param $key string Key
     * @return value of null.
     */
    public function get($key)
    {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : null;
    }

    /**
     * Delete a key-value pair
     * @param $key string Key to be deleted.
     * @return bool if the key-value pair could be deleted, false otherwise.
     */
    public function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * Return the active status of the session.
     * @param $clientSessionId Session ID as passed by the client.
     * @return bool True if the session is active, false otherwise.
     */
    public function isActive($clientSessionId)
    {
        // Check if the ID exists in the session
        if ($clientSessionId != session_id())
        {
            $this->lastMessage = "Invalid client session ID.";
            return false;
        }

        $userID = $this->get("UserID");
        if ($userID == null)
        {
            $this->lastMessage = "No user in session.";
            return false;
        }

        $this->lastMessage = "";
        return true;
    }

    /**
     * Get current session ID.
     * @return string Session ID.
     */
    public function getSessionID()
    {
        return session_id();
    }
}
