<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * constructor
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT token via given credentials.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $credentials = $request->only('email', 'password');

        $token = $this->guard()->attempt($credentials);
        if ($token != null) {
            return [
                'token' => $this->respondWithToken($token)->getData(),
                'loggedUser' => $this->getLoggedUser()->getData()
            ];
        }

        return response()->json(['error' => 'Bad credentials.'], 401);
    }

    /**
     * Get the authenticated User
     */
    public function getLoggedUser()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out!']);
    }

    /**
     * Refresh a token.
     * After execution the old token becomes invalid.
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     */
    public function guard(): Guard
    {
        return Auth::guard();
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $name = $request->name;
        $surname = $request->surname;
        $password = Hash::make($request->password);
        $email = $request->email;
        $role = "user";

        //checks if there are existing users using the same email
        $user = User::where('email', '=', $request->email)->first();

        if ($user === null) {
            $response = User::create(compact('name', 'surname', 'email', 'password', 'role'));
            return $response;
        } else {
            return response()->json(['error' => 'That email is taken. Try another'], 409);
        }
    }
}
