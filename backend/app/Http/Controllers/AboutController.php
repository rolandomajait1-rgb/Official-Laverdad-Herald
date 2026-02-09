<?php

namespace App\Http\Controllers;

use App\Models\Staff;

class AboutController extends Controller
{
    public function index()
    {
        $staff = Staff::with('user')->get();
        return view('about.index', compact('staff'));
    }
}

