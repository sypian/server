<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * A trait to handle json responses meant for controllers.
 *
 * addToPayload() - Add a key/value pair in the json payload.
 * addError() - Add an error message.
 * generateJsonResponse() - Merge payload and errors and generate a json response.
 *
 * Errors will be gathered in an "errors" array in the resulting response generated by generateJsonResponse().
 * If you use the key "errors" before it will be overwritten.
 */
trait JsonResponseTrait
{
    protected $payload = [];
    protected $errors = [];

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
