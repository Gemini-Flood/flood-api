<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    /**
     * Generate a JSON response with error, code, data, and message.
     *
     * @param bool $error - Indicates if there is an error.
     * @param int $code - The response code.
     * @param mixed $data - The response data.
     * @param string $message - The response message.
     * @return \Illuminate\Http\JsonResponse - The generated JSON response.
    */
    public function globalResponse($error, $code, $data, $message)
    {
        return response()->json([
            'error' => $error,
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ]);
    }
}
