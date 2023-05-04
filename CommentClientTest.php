<?php

declare(strict_types=1);

namespace myth21\lib;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

use function count;
use function explode;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * This is a PHP unit test script which tests the `CommentClient` class.
 * The `CommentClient` class is responsible for handling the CRU operations for comments on a server.
 * The script sets up a `HttpRequester` object and a URL to be used for testing.
 * The script contains several test functions that check whether the methods of the `CommentClient` class are functioning as expected.
 * For example, the `testGet` function calls the `sendGetRequest` method to retrieve comments from the server, and checks whether the response is valid and contains expected data.
 * The `testPost` function sends a new comment to the server using the `sendPostRequest` method, and checks whether the response is valid and contains the expected data.
 * The `testPut` function tests the `sendPutRequest` method which updates an existing comment on the server.
 * In addition, there are helper functions such as `getDecodedResponse` which is used to decode JSON responses received from the server.
 */
class CommentClientTest extends TestCase
{
    /**
     * Test const, record id is created on server.
     */
    private const STORAGE_CREATED_ID = 102;

    protected static HttpRequesterInterface $requester;

    protected static string $url;

    public static function setUpBeforeClass(): void
    {
        require 'ContextHttpRequester.php';
        self::$requester = new ContextHttpRequester();
        $urlForTesting = getenv('UrlForTesting');
        if (!is_string($urlForTesting)) {
            $exceptionMessage = 'Please create file phpunit.xml, set php env var UrlForTesting="https://examle.com"';
            $exceptionMessage .= ' and specify path to the file "-c /path/to/phpunit.xml" on run the phpunit tests.';
            throw new RuntimeException($exceptionMessage);
        }
        self::$url = $urlForTesting;
    }

    /**
     * @throws JsonException
     */
    public function testGet(): void
    {
        $response = self::$requester->sendGetRequest(self::$url);
        $decodedResponse = $this->getDecodedResponse($response);

        $this->assertEquals('GET', $decodedResponse['method']);
        $this->assertEquals(200, self::$requester->getLastResponseCode());
        $this->assertIsArray($decodedResponse['comments']);

        foreach ($decodedResponse['comments'] as $comment) {
            $this->assertArrayHasKey('id', $comment);
            $this->assertIsInt($comment['id']);

            $this->assertArrayHasKey('name', $comment);
            $this->assertIsString($comment['name']);

            $this->assertArrayHasKey('text', $comment);
            $this->assertIsString($comment['text']);
        }
    }

    /**
     * @throws JsonException
     */
    public function testPost(): void
    {
        $data = [
            'id' => null,
            'name' => 'Bob',
            'text' => 'Hello, World',
        ];
        $response = self::$requester->sendPostRequest(self::$url, $data);
        $decodedResponse = $this->getDecodedResponse($response);

        $this->assertEquals('POST', $decodedResponse['method']);
        $this->assertEquals(201, self::$requester->getLastResponseCode());
        $this->assertEquals(self::STORAGE_CREATED_ID, $decodedResponse['comment']['id']);

        $this->assertArrayHasKey('name', $decodedResponse['comment']);
        $this->assertArrayHasKey('text', $decodedResponse['comment']);

        $this->assertEquals($data['name'], $decodedResponse['comment']['name']);
        $this->assertEquals($data['text'], $decodedResponse['comment']['text']);
    }

    /**
     * @throws JsonException
     */
    public function testPut(): void
    {
        $data = [
            'name' => 'Alice',
            'text' => 'Hi, everyone',
        ];
        // It is assumed that the server has no routing clean URLs.
        self::$requester->sendPutRequest(self::$url . '?id=102', $data);
        $this->assertEquals(204, self::$requester->getLastResponseCode());
    }

    /**
     * @throws JsonException
     */
    private function getDecodedResponse(string $response)
    {
        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }

    public function testReturnDecodedResponse(): void
    {
        self::$requester->setIsReturnDecodedResponse(true);
        $this->assertTrue(self::$requester->isReturnDecodedResponse());

        self::$requester->setIsReturnDecodedResponse(false);
        $this->assertFalse(self::$requester->isReturnDecodedResponse());
    }

    public function testRequestHeaders(): void
    {
        $headerKey = 'Authorization';
        $headerValue = 'token';

        $this->assertArrayNotHasKey($headerKey, self::$requester->getRequestHeaders());

        self::$requester->setRequestHeader($headerKey, $headerValue);

        $this->assertArrayHasKey($headerKey, self::$requester->getRequestHeaders());
        $this->assertEquals($headerValue, self::$requester->getRequestHeader($headerKey));

        self::$requester->deleteRequestHeader($headerKey);

        $this->assertArrayNotHasKey($headerKey, self::$requester->getRequestHeaders());
    }

    public function testGetRawHeaders(): void
    {
        $class = new ReflectionClass(self::$requester);
        $method = $class->getMethod('getPreparedRequestHeaders');
        $method->setAccessible(true);
        $preparedRequestHeaders = $method->invoke(self::$requester);

        $requestHeaders = [];
        foreach ($preparedRequestHeaders as $preparedRequestHeader) {
            $exploded = explode(':', $preparedRequestHeader);
            $this->assertEquals(self::$requester->getRequestHeader($exploded[0]), $exploded[1]);
            $requestHeaders[] = $preparedRequestHeader;
        }
        $this->assertCount(count($requestHeaders), self::$requester->getRequestHeaders());
    }

    public function testSetUseIncludePath(): void
    {
        self::$requester->setUseIncludePath(true);
        $this->assertTrue(self::$requester->isUseIncludePath());

        self::$requester->setUseIncludePath(false);
        $this->assertFalse(self::$requester->isUseIncludePath());
    }
}