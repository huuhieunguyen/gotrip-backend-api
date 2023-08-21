<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * Create a success response.
     *
     * @param mixed  $data
     * @param string $message
     * @param int    $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $message, $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Create an error response.
     *
     * @param string       $message
     * @param array|string $errorMessages
     * @param int          $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $errorMessages = [], $statusCode = 400)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $statusCode);
    }
}
