<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller{
    public function signup(Request $request)
    {
        try {
            $isValidated = Validator::make($request->all(), [
                "name" => "required|string|max:255",
                "email" => "required|string|email|max:255|unique:users",
                "password" => [
                    "required",
                    "string",
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                ],
            ]);

            if ($isValidated->fails()) {
                return errorMessageResponse(false, "Validation Error", $isValidated->errors(), 401);
            }

            $user = new User;
            $user->name = $request["name"];
            $user->email = $request["email"];
            $user->password = bcrypt($request["password"]);
            $user->save();
            
            $token = $user->createToken("token")->accessToken;
            $user->token = $token;

            return loginMessageResponse(true, "User Signed Up Successfully", $user, 200);

        } catch (\Throwable $e) {
            return errorMessageResponse(false, null, $e->getMessage(), 500);
        }
    }

    public function login(Request $request){
        try {
            $isValidated = Validator::make($request->all(), [
                "email" => "required|string|email",
                "password" => "required|string",
            ]);

            if ($isValidated->fails()) {
                return errorMessageResponse(false, $isValidated->errors(), "Unauthenticated User", 401);
            }

            $user = User::where("email", $request["email"])->first();

            if (!$user || !Hash::check($request["password"], $user->password)) {
                return errorMessageResponse(false, null, "Invalid credentials", 401);
            }

            $token = $user->createToken("token")->accessToken;
            $user->token = $token;

            return loginMessageResponse(true, "Login Successful", $user, 200);
        } catch (\Throwable $e) {
            return errorMessageResponse(false, null, $e->getMessage(), 401);
        }
    }
}
