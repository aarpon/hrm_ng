<?php
/**
 * Created by PhpStorm.
 * User: aaron
 * Date: 17/07/15
 * Time: 19:38
 */

namespace hrm\RPC;


class JSONRPCServer
{

    /**
     * @var string HTTP response status
     */
    private $httpReponseStatus = "200 OK";

    /**
     * @var string HTTP response content (JSON array).
     */
    private $httpResponseContent = "";

    /**
     * @var string Response content type: "application/json;charset=utf8"
     */
    private $httpResponseContentType = "application/json;charset=utf8";

    /**
     * @var string JSON RPC request sent from the client.
     */
    private $httpRequest = "";

    /**
     * @var string Last error message.
     */
    private $lastError = "";

    /**
     * @var bool Flag that indicates whether the JSON RPC request is valid.
     */
    private $isRequestValid = false;

    /**
     * @var int|string PHP session ID sent from the client.
     */
    private $clientId = -1;

    /**
     * @var string PHP session ID on the server as returned by php_session_id().
     */
    private $phpSessionId = "";

    /**
     * @var string Invoked method.
     */
    private $method = "";

    /**
     * @var null|array null of array of parameters for the invoked method.
     */
    private $params = null;

    /**
     * Constructor
     * @param $request string JSON RPC request from the client.
     * @param $phpSessionID string Currently active session ID.
     */
    public function __construct($request, $phpSessionID)
    {
        // Store the request
        $this->httpRequest = $request;

        // Store the PHP session ID
        $this->phpSessionId = $phpSessionID;

        // Validate it
        $this->validateRequest();
    }

    /**
     * Execute the method.
     */
    public function executeRequest()
    {

    }

    /**
     * Return the response status.
     *
     * The response status is something in the form: "200 OK"
     *
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     *
     * @return string response status.
     */
    public function getResponseStatus()
    {
        return $this->httpReponseStatus;
    }

    /**
     * Return the response body.
     *
     * The response status is a JSON array.
     *
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     *
     * @return string response content (a JSON array).
     */
    public function getResponseBody()
    {
        return $this->httpReponseStatus;
    }

    /**
     * Return the response content type.
     *
     * The response status is a JSON array.
     *
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     *
     * @return string response content (a JSON array).
     */
    public function getResponseContentType()
    {
        return $this->httpResponseContentType;
    }

    /**
     * Return the response content length.
     *
     * Build the header as following:
     *
     * header("HTTP/1.1 " . $server->getResponseStatus());
     * header("Content-type: " . $server->getResponseContentType(), true);
     * header("Content-length: " . $server->getResponseContentLength());
     * echo $server->getResponseBody();
     *
     * @return string response content (a JSON array).
     */
    public function getResponseContentLength()
    {
        return mb_strlen($this->httpResponseContent);
    }

    /**
     * Validate the JSON RPC request from the client.
     */
    private function validateRequest()
    {
        // Do we have a JSON-RPC 2.0 request?
        if (!(array_key_exists('jsonrpc', $this->httpRequest) &&
            $this->httpRequest['jsonrpc'] == "2.0")) {

            // Invalid JSON-RPC 2.0 call
            $this->lastError = "Invalid JSON-RPC 2.0 call.";

            // Flag invalid
            $this->isRequestValid = false;

            // TODO Set the response code
            $this->httpReponseStatus = "400 <INCOMPLETE>";

            // Return here
            return;

        };

        // The session ID must be present. If a session does not yet exist,
        // it should be set to -1.
        if (!(array_key_exists('id', $this->httpRequest))) {

            // Session ID missing
            $this->lastError = "Session ID is missing!";

            // Flag invalid
            $this->isRequestValid = false;

            // TODO Set the response code
            $this->httpReponseStatus = "400 <INCOMPLETE>";

            // Return here
            return;
        }

        // Retrieve the session ID from the client
        $this->clientId = $this->httpRequest['id'];

        // The method is mandatory
        if (!isset($this->httpRequest['method'])) {

            // Expected method
            $this->lastError = "Expected method!";

            // Flag invalid
            $this->isRequestValid = false;

            // TODO Set the response code
            $this->httpReponseStatus = "400 <INCOMPLETE>";

            // Return here
            return;
        }

        // Check that the method is valid
        if (! $this->isMethodValid())
        {
            // Invalid method
            $this->lastError = "Invalid method!";

            // Flag invalid
            $this->isRequestValid = false;

            // TODO Set the response code
            $this->httpReponseStatus = "400 <INCOMPLETE>";

            // Return here
            return;
        }

        // Store the method
        $this->method = $this->httpRequest['method'];

        // Method parameters
        $this->params = null;
        if (array_key_exists('params', $this->httpRequest)) {

            // Set the parameters
            $this->params = $this->httpRequest['params'];

            // Che that the parameters are valid
            if (! $this->areParametersValid())
            {
                // Invalid parameters
                $this->lastError = "Invalid parameters!";

                // Flag invalid
                $this->isRequestValid = false;

                // TODO Set the response code
                $this->httpReponseStatus = "400 <INCOMPLETE>";

                // Return here
                return;
            }
        }

    }

    /**
     * Check if the method is valid.
     * @return bool True if the method is valid, false otherwise.
     */
    private function isMethodValid()
    {
        return true;
    }

    /**
     * Check if the parameters for currently set method are valid.
     * @return bool True if the parameters are valid, false otherwise.
     */
    private function areParametersValid()
    {
        return true;
    }

}
