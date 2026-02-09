<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function index()
    {
        return TeamMember::all();
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image' => 'nullable|string'
        ]);

        $member = TeamMember::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'role' => $request->role,
                'image' => $request->image
            ]
        );

        return response()->json($member);
    }
}
