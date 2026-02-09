<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,moderator,editor,author,subscriber',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => 'User',
            'model_id' => $user->id,
            'new_values' => $user->toArray(),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,moderator,editor,author,subscriber',
        ]);

        $oldValues = $user->toArray();

        $user->update($request->only(['name', 'email', 'role']));

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $user->toArray(),
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $oldValues = $user->toArray();

        $user->delete();

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function getModerators()
    {
        $moderators = User::where('role', 'moderator')->get();
        return response()->json($moderators)
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function addModerator(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Create new user if doesn't exist
            $user = User::create([
                'name' => explode('moderator@example.com', $request->email)[0],
                'email' => $request->email,
                'password' => Hash::make('password123'), // Default password
                'role' => 'moderator',
            ]);

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'created',
                'model_type' => 'User',
                'model_id' => $user->id,
                'new_values' => $user->toArray(),
            ]);

            return response()->json(['message' => 'Moderator created successfully'])
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        if ($user->role === 'moderator') {
            return response()->json(['message' => 'User is already a moderator'], 400)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        $oldValues = $user->toArray();
        $user->update(['role' => 'moderator']);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $user->toArray(),
        ]);

        return response()->json(['message' => 'Moderator added successfully'])
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function removeModerator($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'moderator') {
            return response()->json(['message' => 'User is not a moderator'], 400)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        $oldValues = $user->toArray();
        $user->update(['role' => 'subscriber']); // Default role

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $user->toArray(),
        ]);

        return response()->json(['message' => 'Moderator removed successfully'])
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }
}
