<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $payload = [];
    protected $errors = [];

    /**
     * Returns the given strings formatted in double quotes and separated by a comma and space.
     *
     * @return string
     */
    public function getFormattedParams(array $params): string
    {
        $paramStr = '';

        foreach ($params as $paramKey) {
            $paramStr .= '"'.str_replace('"', '\"', $paramKey).'", ';
        }

        return trim($paramStr, ', ');
    }

    /**
     * Adds a key/value pair to the json payload.
     *
     * @param mixed $value Any value to be saved.
     * @param string $key The key as a string to be used. A second call with the same key will overwrite the
     * previous one.
     */
    public function addToPayload($value, string $key = 'message')
    {
        $this->payload[$key] = $value;
    }

    public function addError(string $message)
    {
        $this->errors[] = [
            'message' => $message,
        ];
    }

    /**
     * Generates and returns a json response using the set payload and errors.
     *
     * addtoPayload() and addError() functions should be used befor calling this.
     *
     * @param int $statusCode The status code to be used for the response.
     */
    public function generateJsonResponse(int $statusCode): JsonResponse
    {
        $responseJson = $this->payload;
        $responseJson['errors'] = $this->errors;
        return response()->json($responseJson, $statusCode);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
