<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * User Controller
 */
class UserController extends Controller
{
    /**
     * Create a new UserController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
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
        $user = DB::table('users')->find($id);

        if ($user == null) {
            return "User with id " . $id . " is not found.";
        }

        return $user;
    }

    /**
     * delete by id
     */
    public function deleteUserById($id): string
    {
        $user = DB::table('users')->find($id);

        if ($user == null) {
            return "User with id " . $id . " does not exist.";
        }

        DB::table('users')->delete($id);
        return "User with id " . $id . " deleted successfully.";
    }


    /**
     * Reset password
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse|void
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i',
            'oldPassword' => 'required|min:8',
            'newPassword' => 'required|min:8',
            'confirmNewPass' => 'required|min:8',
        ],
        [
            'newPassword.min' => 'This field must be at least 8 characters.'
        ]);

        $name = $request->name;
        $surname = $request->surname;
        $email = $request->email;
        $oldPassword = $request->oldPassword;
        $newPassword = $request->newPassword;
        $confirmNewPass = $request->confirmNewPass;

        $user = $this->getUserById($id);  //todo: this is only for admin.
        $role = $user->role;

        if (Hash::check($oldPassword, $user->password) == false) {
            return response()->json(['error' => 'Old password is not correct.'], 400);
        }

        if ($newPassword != $confirmNewPass) {
            return response()->json(['error' => 'Passwords does not match.'], 400);
        }

        if ($oldPassword == $newPassword) {
            return response()->json(['error' => 'New password cannot be the same as the old password.'], 400);
        }

        $existingUser = User::where('email', '=', $request->email)->first();
        if ($existingUser != null) {
            return response()->json(['error' => 'That email is taken. Try another'], 409);
        }

        $password = Hash::make($newPassword);

        User::update(compact('name', 'surname', 'email', 'password', 'role'));
//        User::where('id', $id)->update(array('password' => $password));

//        AuthController->logout();

        return response()->json(['data' => 'Updated successfully'], 204);
    }
}
