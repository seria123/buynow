<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthenticateSessionController extends Controller
{
    public function index()
    {
        return Inertia::render('Auth/Login');
    }
}
