<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\User;
use App\Http\Controllers\BaseController;

class UserController extends BaseController
{

    protected $userData = false;
    // public function __construct() {
    //     $user = $this->getAuthenticatedUser();
    //     if($user['error'] === false) {
    //         $this->userData = $user['data'];
    //     }
    // }
    
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => true,
                    'message' => 'invalid_credentials'
                ]);
                // return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => true,
                'message' => 'could_not_create_token'
            ]);
            // return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function logout(Request $request){
        $token =  $request->header('Authorization');
        try {
            JWTAuth::parseToken()->invalidate($token);
            return response()->json([
                'error' => false,
                'message' => 'You have successfully logged out.'
            ]);
            // return response()->json(['success' => true, 'message'=> "You have successfully logged out."]);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'error' => true,
                'message' => 'Failed to logout, please try again.'
            ]);
            // return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
        
        // return response()->json([
        //     'statusCode' => 200,
        //     'statusMessage' => 'success',
        //     'message' => 'User Logged Out',
        // ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'error' => false,
            'message' => compact('user','token')
        ]);
        // return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'error' => true,
                    'message' => 'user_not_found'
                ]);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json([
                'error' => true,
                'message' => 'token_expired'
            ]);

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json([
                'error' => true,
                'message' => 'token_invalid'
            ]);

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json([
                'error' => true,
                'message' => 'token_absent'
            ]);

        }

        $this->userData = compact('user')['user'];

        return [
            'error' => false,
            'data' => compact('user')['user']
        ];
    }
}