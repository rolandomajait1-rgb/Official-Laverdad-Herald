<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::with('user')->paginate(10);
        return response()->json($staff)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,moderator,editor',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        Staff::create([
            'user_id' => $user->id,
            'role' => $request->role,
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => 'Staff',
            'model_id' => $user->id,
            'new_values' => ['name' => $request->name, 'email' => $request->email, 'role' => $request->role],
        ]);

        return redirect()->route('staff.index')->with('success', 'Staff member created successfully.');
    }

    public function show(Staff $staff)
    {
        return view('staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->user_id,
            'role' => 'required|in:admin,moderator,editor',
        ]);

        $oldValues = [
            'name' => $staff->user->name,
            'email' => $staff->user->email,
            'role' => $staff->role,
        ];

        $staff->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        $staff->update(['role' => $request->role]);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'Staff',
            'model_id' => $staff->user_id,
            'old_values' => $oldValues,
            'new_values' => ['name' => $request->name, 'email' => $request->email, 'role' => $request->role],
        ]);

        return redirect()->route('staff.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        $oldValues = [
            'name' => $staff->user->name,
            'email' => $staff->user->email,
            'role' => $staff->role,
        ];

        $staff->user->delete();
        $staff->delete();

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => 'Staff',
            'model_id' => $staff->user_id,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('staff.index')->with('success', 'Staff member deleted successfully.');
    }
}
