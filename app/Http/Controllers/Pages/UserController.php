<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Example method: show all users
    public function index()
    {
        // You can fetch users from the User model
        // $users = \App\Models\User::all();
        // return view('pages.users.index', compact('users'));

        return "UserController index method works!";
    }

    // Example method: show a single user
    public function show($id)
    {
        // $user = \App\Models\User::findOrFail($id);
        // return view('pages.users.show', compact('user'));

        return "Showing user with ID: $id";
    }
}