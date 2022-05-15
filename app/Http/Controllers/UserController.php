<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * User Controller
 */
class UserController extends Controller
{
    /**
     * @var AuthController
     */
    private $authController;

    /**
     * constructor
     */
    public function __construct(AuthController $authController)
    {
        $this->middleware('auth:api');
        $this->authController = $authController;
    }

    /**
     * list all
     */
    public function listUsers(): Collection
    {
        return DB::table('users')->select('users.*')->get();
    }

    /**
     * get by id
     */
    public function getUserById($id)
    {
        $loggedUser = json_decode($this->authController->getLoggedUser()->content());
        if ($id != $loggedUser->id && $loggedUser->role == 'user') {
            return response()->json(['error' => 'Only admin can access /user/get/{id}.'], 401);
        }

        $user = DB::table('users')->find($id);

        if ($user == null) {
            return response()->json(['error' => "User with id " . $id . " is not found."], 404);
        }

        return $user;
    }

    /**
     * delete by id
     */
    public function deleteUserById($id)
    {
        $user = DB::table('users')->find($id);

        if ($user == null) {
            return response()->json(['error' => "User with id " . $id . " is not found."], 404);
        }

        DB::table('users')->delete($id);
        return response()->json(['message' => "User with id " . $id . " deleted successfully."]);
    }


    /**
     * Reset password
     *
     * @param Request $request
     * @param $id
     */
    public function update(Request $request, $id)
    {
        $loggedUser = json_decode($this->authController->getLoggedUser()->content());
        if ($id != $loggedUser->id) {
            return response()->json(['error' => 'The given id is incorrect.'], 401);
        }

        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
        ]);

        $name = $request->name;
        $surname = $request->surname;
        $email = $request->email;

        $existingUser = User::where('email', '=', $request->email)->first();
        if ($existingUser != null && $existingUser->id != $id) {
            return response()->json(['error' => 'That email is taken. Try another'], 409);
        }

        $user = $this->getUserById($id);
        $updatedUser = ['name' => $name, 'surname' => $surname, 'email' => $email, 'password' => $user->password];

        //password reset
        if ($request->oldPassword != null) {
            $request->validate([
                'oldPassword' => 'required|min:8',
                'newPassword' => 'required|min:8',
                'confirmNewPass' => 'required|min:8',
            ], [
                'newPassword.min' => 'This field must be at least 8 characters.'
            ]);

            $oldPassword = $request->oldPassword;
            $newPassword = $request->newPassword;
            $confirmNewPass = $request->confirmNewPass;

            if (Hash::check($oldPassword, $user->password) == false) {
                return response()->json(['error' => 'Old password is not correct.'], 400);
            }

            if ($newPassword != $confirmNewPass) {
                return response()->json(['error' => 'Passwords does not match.'], 400);
            }

            if ($oldPassword == $newPassword) {
                return response()->json(['error' => 'New password cannot be the same as the old password.'], 400);
            }

            $password = Hash::make($newPassword);
            $updatedUser = ['name' => $name, 'surname' => $surname, 'email' => $email, 'password' => $password];
        }

        DB::table('users')->where('id', $id)->update($updatedUser);

        //invalidate token ONLY after password reset Or email change
        if ($updatedUser['password'] != $user->password || $updatedUser['email'] != $user->email) {
            $token = $request->bearerToken();
            Auth::setToken($token)->invalidate();
        }

        return response()->json(['message' => 'User updated successfully.']);
    }
}
