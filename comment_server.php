<?php

/**
 * Warning: the server processes requests with Content-Type: application/json.
 */

declare(strict_types=1);

$responseData['method'] = $_SERVER['REQUEST_METHOD'];

try {
    switch ($responseData['method']) {

        case 'GET':
            $responseData['comments'] = [
                [
                    'id' => 100,
                    'name' => 'Bob',
                    'text' => 'Hello, World',
                ],
                [
                    'id' => 101,
                    'name' => 'Alice',
                    'text' => 'Hi, everyone',
                ],
            ];
            break;

        case 'POST':

            // Create logic here.
            $inputData = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
            $responseData['comment'] = $inputData;
            $responseData['comment']['id'] = 102;
            http_response_code(201);
            break;

        case 'PUT':

            // Please take {id} from request url.
            // Update logic here.
            http_response_code(204);
            break;

        default:

            $responseData['error']['message'] = 'Request method is undefined';
    }

    header('Content-Type: application/json');
    echo json_encode($responseData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // Do error logging here.
}