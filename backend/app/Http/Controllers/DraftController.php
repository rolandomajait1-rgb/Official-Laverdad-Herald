<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAdmin()) {
            $drafts = Draft::with('author.user')->paginate(10);
        } else {
            $drafts = Draft::with('author.user')->where('author_id', Auth::user()->author->id)->paginate(10);
        }
        return response()->json($drafts)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function create()
    {
        return view('drafts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $draft = Draft::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'author_id' => Auth::user()->author->id,
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => 'Draft',
            'model_id' => $draft->id,
            'new_values' => $draft->toArray(),
        ]);

        return redirect()->route('drafts.index')->with('success', 'Draft created successfully.');
    }

    public function show(Draft $draft)
    {
        return view('drafts.show', compact('draft'));
    }

    public function edit(Draft $draft)
    {
        return view('drafts.edit', compact('draft'));
    }

    public function update(Request $request, Draft $draft)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $oldValues = $draft->toArray();

        $draft->update($request->all());

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'Draft',
            'model_id' => $draft->id,
            'old_values' => $oldValues,
            'new_values' => $draft->toArray(),
        ]);

        return redirect()->route('drafts.index')->with('success', 'Draft updated successfully.');
    }

    public function destroy(Draft $draft)
    {
        $oldValues = $draft->toArray();

        $draft->delete();

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => 'Draft',
            'model_id' => $draft->id,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('drafts.index')->with('success', 'Draft deleted successfully.');
    }
}
