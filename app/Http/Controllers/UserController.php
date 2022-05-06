<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
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
        if ($id != $loggedUser ->id && $loggedUser ->role == 'user'){
            return response()->json(['error' => 'Only admin can access /users/{id}.'], 401);
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
        return "User with id " . $id . " deleted successfully.";
    }


    /**
     * Reset password
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function resetPassword(Request $request, $id): JsonResponse
    {
        $loggedUser = json_decode($this->authController->getLoggedUser()->content());
        if ($id != $loggedUser ->id){
            return response()->json(['error' => 'The given id is incorrect.'], 401);
        }

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

        $user = $this->getUserById($id);

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

        if ($existingUser != null && $existingUser->id != $id) {
            return response()->json(['error' => 'That email is taken. Try another'], 409);
        }

        $password = Hash::make($newPassword);
        $updatedUser = ['name' => $name, 'surname' => $surname, 'email' => $email, 'password' => $password];
        DB::table('users')->where('id', $id)->update($updatedUser);

//        if ($password != null){
            Auth::logout();
//        }

        return response()->json(['message' => 'Updated successfully.']);
    }
}
