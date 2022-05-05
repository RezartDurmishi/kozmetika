<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
//        $this->middleware(['auth:api', 'admin'], ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT token via given credentials.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = $this->guard()->attempt($credentials);
        if ($token != null) {
            return $this->respondWithToken($token);
        }
        return response()->json(['error' => 'Bad credentials.'], 401);
    }

    /**
     * Get the authenticated User
     */
    public function getLoggedUser(): JsonResponse
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     */
    public function logout(): JsonResponse
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken(string $token): JsonResponse
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


     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i',
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
            return response()->json(['data' => $response], 201);
        } else {
            return response()->json(['error' => 'That email is taken. Try another'], 409);
        }
    }
}
