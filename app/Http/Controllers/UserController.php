<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->messages()->first(),
                    'status_code' => 500
                ]);
            } else {
                $user = new User($request->all());
                $user->password = Hash::make($request->password);
                if ($user->save()) {
                    // $credentials = $user->login();
                    return response()->json([
                        'error' => false,
                        'message' => 'Successfully created user',
                        'status_code' => 200,
                        'data' => $user,
//                        'login' => [
//                            $credentials->original
//                        ]
                    ]);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'something went wrong',
                        'status_code' => 500
                    ]);
                }
            }

        } catch (\Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => 'something went wrong',
                'dev_message' => $exception->getMessage()
            ]);
        }

    }
}
