# A class to send HTTP requests

Features:
Class abstracts the HTTP communication through stream contexts in PHP.
It provides convenient methods to send GET, POST, and PUT requests, and set request headers.
The class stores request headers and response headers of the last request sent for further inspection.
It also has a configuration option to decode the response or not.
The class is extensible to implement other HTTP methods as well.
One thing that could be improved is the lack of error handling.
For example, the code assumes that the file contents of the response will always be a string, which may not be true if there was an error.
Another potential concern is that this class may be too simple for some situations.
It doesn't provide functionality for customizing the HTTP status code that is sent in the response, setting cookies, or handling redirects, for example.
Overall, this code provides a lightweight way to perform simple HTTP requests in PHP, but it may not be suitable for more complex situations.

## Requirements

* PHP >= 8.0
* PHPUnit >= 9.3

## Install

```
composer require myth21/lib:dev-main
```

## Usage

Get entity list:
```
$contextHttpRequester = new ContextHttpRequester();

$contextHttpRequester->sendGetRequest('http://example.com/comments/');
```

Create entity:
```
$contextHttpRequester->sendPostRequest('http://example.com/comments/', [
    'id' => null,
    'name' => 'Bob',
    'text' => 'Hello, World',
]);
```

Update entity:
```
$contextHttpRequester->sendPutRequest('http://example.com/comments/123/', [
    'name' => 'Alice',
    'text' => 'Hi, everyone',
]);
```

## Testing

Require phpunit.

Optionality you can run server from directory _comment_server.php_ for testing by command:
```
php -S localhost:8000 -f comment_server.php
```

Run test, example:
```
php vendor/bin/phpunit vendor/myth21/lib/CommentClientTest.php -c vendor/myth21/lib/example.phpunit.xml --color --do-not-cache-result
```