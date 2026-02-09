<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\User;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::with('user')->paginate(10);
        return view('authors.index', compact('authors'));
    }

    public function create()
    {
        return view('authors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'bio' => 'nullable|string',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $author = Author::create([
            'user_id' => $user->id,
            'bio' => $request->bio,
            'website' => $request->website,
            'social_links' => $request->social_links,
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => 'Author',
            'model_id' => $author->id,
            'new_values' => $request->all(),
        ]);

        return redirect()->route('authors.index')->with('success', 'Author created successfully.');
    }

    public function show(Author $author)
    {
        return view('authors.show', compact('author'));
    }

    // Public-facing: view author by user id
    public function publicShow(int $userId)
    {
        $author = Author::where('user_id', $userId)->firstOrFail();
        $articles = \App\Models\Article::published()
            ->where('author_id', $author->id)
            ->with('author.user', 'categories', 'tags')
            ->latest('published_at')
            ->paginate(10);

        return view('authors.public', compact('author', 'articles'));
    }

    public function edit(Author $author)
    {
        return view('authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $author->user_id,
            'bio' => 'nullable|string',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        $oldValues = [
            'name' => $author->user->name,
            'email' => $author->user->email,
            'bio' => $author->bio,
            'website' => $author->website,
            'social_links' => $author->social_links,
        ];

        $author->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $author->update([
            'bio' => $request->bio,
            'website' => $request->website,
            'social_links' => $request->social_links,
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'Author',
            'model_id' => $author->user_id,
            'old_values' => $oldValues,
            'new_values' => $request->all(),
        ]);

        return redirect()->route('authors.index')->with('success', 'Author updated successfully.');
    }

    public function destroy(Author $author)
    {
        $oldValues = [
            'name' => $author->user->name,
            'email' => $author->user->email,
            'bio' => $author->bio,
            'website' => $author->website,
            'social_links' => $author->social_links,
        ];

        $author->user->delete();
        $author->delete();

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => 'Author',
            'model_id' => $author->user_id,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('authors.index')->with('success', 'Author deleted successfully.');
    }
}
