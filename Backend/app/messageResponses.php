<?php

function loginMessageResponse($success, $message, $response, $statusCode)
{
    return response()->json([
        "success" => $success,
        "message" => $message,
        "data" => $response
    ], $statusCode);
}

function messageResponse($success, $message, $statusCode, $data = null)
{
    return response()->json([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], $statusCode);
}

function errorMessageResponse($success, $error, $message, $statusCode){
    return response()->json([
        "success" => $success,
        "error" => $error,
        "message" => $message
    ], $statusCode);
}