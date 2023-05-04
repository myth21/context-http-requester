<?php

declare(strict_types=1);

namespace myth21\lib;

use JsonException;

/**
 * Class abstracts the HTTP communication through stream contexts in PHP.
 * It provides convenient methods to send GET, POST, and PUT requests, and set request headers.
 * The class stores request headers and response headers of the last request sent for further inspection.
 * It also has a configuration option to decode the response or not.
 * The class is extensible to implement other HTTP methods as well.
 * One thing that could be improved is the lack of error handling.
 * For example, the code assumes that the file contents of the response will always be a string, which may not be true if there was an error.
 * Another potential concern is that this class may be too simple for some situations.
 * It doesn't provide functionality for customizing the HTTP status code that is sent in the response, setting cookies, or handling redirects, for example.
 * Overall, this code provides a lightweight way to perform simple HTTP requests in PHP, but it may not be suitable for more complex situations.
 */
final class ContextHttpRequester implements HttpRequesterInterface
{
    /**
     * Request headers. You can set or delete not need headers.
     */
    private array $requestHeaders = [
        'Content-Type' => 'application/json'
    ];

    /**
     * Response headers. Property is saving headers of the last request.
     */
    private array $responseHeaders = [];

    /**
     * To search for a file in include path. It is not needed for HTTP requests.
     */
    private bool $useIncludePath = false;

    /**
     * Should return the decoded response or no.
     */
    private bool $isReturnDecodedResponse = true;

    /**
     * Sets whether to return the decoded response.
     */
    public function setIsReturnDecodedResponse(bool $value): void
    {
        $this->isReturnDecodedResponse = $value;
    }

    /**
     * Whether to decode the response.
     */
    public function isReturnDecodedResponse(): bool
    {
        return $this->isReturnDecodedResponse;
    }

    /***
     * Return the last HTTP response headers executed.
     */
    public function getLastHttpResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /***
     * Return the last HTTP response code executed.
     */
    public function getLastResponseCode(): ?string
    {
        if (empty($this->responseHeaders)) {
            $this->responseHeaders = $this->getLastHttpResponseHeaders();
        }

        if (isset($this->responseHeaders[0])) {
            return substr($this->responseHeaders[0], 9, 3);
        }

        return null;
    }

    /**
     * Set request header in array by key and value.
     */
    public function setRequestHeader(string $key, string $value): void
    {
        $this->requestHeaders[$key] = $value;
    }

    /**
     * Delete request header by key from array.
     */
    public function deleteRequestHeader(string $key): void
    {
        unset($this->requestHeaders[$key]);
    }

    /**
     * Return request header by key.
     */
    public function getRequestHeader(string $key): ?string
    {
        return $this->requestHeaders[$key] ?? null;
    }

    /**
     * Return all the request headers.
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * Return prepared the headers for request.
     */
    private function getPreparedRequestHeaders(): array
    {
        $preparedHeaders = [];
        foreach ($this->requestHeaders as $key => $value) {
            $preparedHeaders[] = $key . ':' . $value;
        }
        return $preparedHeaders;
    }

    /**
     * Send GET request.
     */
    public function sendGetRequest(string $url): string|false
    {
        $streamContext = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => $this->getPreparedRequestHeaders()
        ]]);

        $response = file_get_contents($url, $this->useIncludePath, $streamContext);
        $this->responseHeaders = $http_response_header;
        return $response;
    }

    /**
     * Send POST request.
     * @throws JsonException
     */
    public function sendPostRequest(string $url, array $body): string|false
    {
        $streamContext = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => $this->getPreparedRequestHeaders(),
            'content' => json_encode($body, JSON_THROW_ON_ERROR),
        ]]);

        $response = file_get_contents($url, $this->useIncludePath, $streamContext);
        $this->responseHeaders = $http_response_header;
        return $response;
    }

    /**
     * Send PUT request.
     * @throws JsonException
     */
    public function sendPutRequest(string $url, array $body): string|false
    {
        $streamContext = stream_context_create(['http' => [
            'method' => 'PUT',
            'header' => $this->getPreparedRequestHeaders(),
            'content' => json_encode($body, JSON_THROW_ON_ERROR),
        ]]);
        $response = file_get_contents($url, $this->useIncludePath, $streamContext);
        $this->responseHeaders = $http_response_header;
        return $response;
    }

    /**
     * Set option where find file. Intended for support.
     */
    public function setUseIncludePath(bool $value): void
    {
        $this->useIncludePath = $value;
    }

    /**
     * Return is the option set.
     */
    public function isUseIncludePath(): bool
    {
        return $this->useIncludePath;
    }

    // TODO you can do any others request methods: PATCH, DELETE..
}
