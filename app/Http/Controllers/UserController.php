<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
}
