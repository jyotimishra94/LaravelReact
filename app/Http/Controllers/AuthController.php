<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
                'status' => 'success',
                'message' => 'User logged in successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

    }

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }

    public function view()
    {
        return response()->json([
            'status' => 'success',
            'users' => User::all(),
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function destroy($id)
    {
        // Find the user by ID
        $user = User::find($id);

        if ($user) {
            // Delete the user
            $user->delete();

            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } else {
            // Return an error response if the user is not found
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
    }

    // public function update(Request $request, $id)
    // {
      
    //     $user = User::find($id);
    //     if ($user) {
    //         // Update the user with the new data
    //         $user->update($request->all());
    
    //         // Return a success response
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'User updated successfully',
    //             'user' => $user // Optionally return the updated user data
    //         ]);
    //     } else {
    //         // Return an error response if the user is not found
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'User not found'
    //         ], 404);
    //     }
    // }

    public function update(Request $request, $id)
{
    // Validate the input data
    $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
        'password' => 'sometimes|required|string|min:6',
    ]);

    // Find the user by ID
    $user = User::find($id);

    // Check if the user exists
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }

    // Prepare the data for update
    $data = $request->all();
    if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
    }

    // Update the user with the new data
    $user->update($data);

    // Return a success response
    return response()->json([
        'status' => 'success',
        'message' => 'User updated successfully',
        'user' => $user // Optionally return the updated user data
    ]);
}


  
    

    

}