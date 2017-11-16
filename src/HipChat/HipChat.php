<?php

namespace Go1\HipChat;

/**
 * Super-simple, minimum abstraction HipChat REST API
 *
 * @see https://developer.atlassian.com/hipchat/guide/hipchat-rest-api
 *
 * @author  An Hoang <an.hoang@go1.com>
 * @version 1.0
 */
class HipChat
{
    private $authToken;
    public $apiEndpoint = 'https://api.hipchat.com';

    /*  SSL Verification
        Read before disabling:
        http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
    */
    public $verifySsl = true;

    private $requestSuccessful = false;
    private $lastError = '';
    private $lastResponse = [];
    private $lastRequest = [];

    /**
     * Create a new instance
     * * @param string $authToken - You can get one here: https://yourCompany.hipchat.com/account/api
     *
     * @throws \Exception
     */
    public function __construct($authToken)
    {
        $this->authToken = $authToken;
        $this->lastResponse = ['headers' => null, 'body' => null];
    }

    /**
     * Was the last request successful?
     *
     * @return bool  True for success, false for failure
     */
    public function success()
    {
        return $this->requestSuccessful;
    }

    /**
     * Get the last error returned by either the network transport, or by the API.
     * If something didn't work, this should contain the string describing the problem.
     *
     * @return  array|false  describing the error
     */
    public function getLastError()
    {
        return $this->lastError ?: false;
    }

    /**
     * Get an array containing the HTTP headers and the body of the API response.
     *
     * @return array  Assoc array with keys 'headers' and 'body'
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Get an array containing the HTTP headers and the body of the API request.
     *
     * @return array  Assoc array
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Make an HTTP DELETE request - for deleting data
     *
     * @param   string $method URL of the API request method
     * @param   array  $args Assoc array of arguments (if any)
     * @param   int    $timeout Timeout limit for request in seconds
     *
     * @return  array|false   Assoc array of API response, decoded from JSON
     */
    public function delete($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('delete', $method, $args, $timeout);
    }

    /**
     * Make an HTTP GET request - for retrieving data
     *
     * @param   string $method URL of the API request method
     * @param   array  $args Assoc array of arguments (usually your data)
     * @param   int    $timeout Timeout limit for request in seconds
     *
     * @return  array|false   Assoc array of API response, decoded from JSON
     */
    public function get($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('get', $method, $args, $timeout);
    }

    /**
     * Make an HTTP PATCH request - for performing partial updates
     *
     * @param   string $method URL of the API request method
     * @param   array  $args Assoc array of arguments (usually your data)
     * @param   int    $timeout Timeout limit for request in seconds
     *
     * @return  array|false   Assoc array of API response, decoded from JSON
     */
    public function patch($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('patch', $method, $args, $timeout);
    }

    /**
     * Make an HTTP POST request - for creating and updating items
     *
     * @param   string $method URL of the API request method
     * @param   array  $args Assoc array of arguments (usually your data)
     * @param   int    $timeout Timeout limit for request in seconds
     *
     * @return  array|false   Assoc array of API response, decoded from JSON
     */
    public function post($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('post', $method, $args, $timeout);
    }

    /**
     * Make an HTTP PUT request - for creating new items
     *
     * @param   string $method URL of the API request method
     * @param   array  $args Assoc array of arguments (usually your data)
     * @param   int    $timeout Timeout limit for request in seconds
     *
     * @return  array|false   Assoc array of API response, decoded from JSON
     */
    public function put($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('put', $method, $args, $timeout);
    }

    /**
     * Performs the underlying HTTP request. Not very exciting.
     *
     * @param         string httpVerb   The HTTP verb to use: get, post, put, patch, delete
     * @param  string $method The API method to be called
     * @param  array  $args Assoc array of parameters to be passed
     * @param  int    $timeout
     *
     * @return array|false Assoc array of decoded result
     * @throws \Exception
     */
    private function makeRequest($httpVerb, $method, $args = [], $timeout = 10)
    {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \Exception("cURL support is required, but can't be found.");
        }

        $url = $this->apiEndpoint . $method;

        $this->lastError = '';
        $this->requestSuccessful = false;
        $response = ['headers' => null, 'body' => null];
        $this->lastResponse = $response;

        $this->lastRequest = [
            'method'  => $httpVerb,
            'path'    => $method,
            'url'     => $url,
            'body'    => '',
            'timeout' => $timeout,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                //'Content-Type: application/x-www-form-urlencoded',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->authToken,
            ]
        );
        curl_setopt($ch, CURLOPT_USERAGENT, 'GO1/HipChat/1.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        switch ($httpVerb) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                $this->attachRequestPayload($ch, $args);
                break;

            case 'get':
                $query = http_build_query($args);
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
                break;

            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            case 'patch':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                $this->attachRequestPayload($ch, $args);
                break;

            case 'put':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->attachRequestPayload($ch, $args);
                break;
        }

        $response['body'] = curl_exec($ch);
        $response['headers'] = curl_getinfo($ch);

        if (isset($response['headers']['request_header'])) {
            $this->lastRequest['headers'] = $response['headers']['request_header'];
        }

        if ($response['body'] === false) {
            $this->lastError = curl_error($ch);
        }

        curl_close($ch);

        return $this->formatResponse($response);
    }

    /**
     * Encode the data and attach it to the request
     *
     * @param   resource $ch cURL session handle, used by reference
     * @param   array    $data Assoc array of data to attach
     */
    private function attachRequestPayload(&$ch, $data)
    {
        $encoded = json_encode($data);
        $this->lastRequest['body'] = $encoded;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    }

    /**
     * Decode the response and format any error messages for debugging
     *
     * @param array $response The response from the curl request
     *
     * @return array|false     The JSON decoded into an array
     */
    private function formatResponse($response)
    {
        $this->lastResponse = $response;

        if (!empty($response['body'])) {

            $d = json_decode($response['body'], true);

            if (isset($d['status']) && $d['status'] != '200' && isset($d['detail'])) {
                $this->lastError = sprintf('%d: %s', $d['status'], $d['detail']);
            }
            else {
                $this->requestSuccessful = true;
            }

            return $d;
        }

        return false;
    }
}
