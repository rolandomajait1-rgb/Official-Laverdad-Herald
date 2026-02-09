<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $articles = \App\Models\Article::latest()->take(5)->get();
        return view('admin.dashboard', compact('articles'));
    }
}
