<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriberController extends Controller
{
    public function index()
    {
        $subscribers = Subscriber::paginate(10);
        return view('subscribers.index', compact('subscribers'));
    }

    public function create()
    {
        return view('subscribers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers',
            'name' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        $data['is_active'] = true;
        $data['subscribed_at'] = now();

        $subscriber = Subscriber::create($data);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => 'Subscriber',
            'model_id' => $subscriber->id,
            'new_values' => $subscriber->toArray(),
        ]);

        return redirect()->route('subscribers.index')->with('success', 'Subscriber created successfully.');
    }

    public function show(Subscriber $subscriber)
    {
        return view('subscribers.show', compact('subscriber'));
    }

    public function edit(Subscriber $subscriber)
    {
        return view('subscribers.edit', compact('subscriber'));
    }

    public function update(Request $request, Subscriber $subscriber)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email,' . $subscriber->id,
            'name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $oldValues = $subscriber->toArray();

        $subscriber->update($request->all());

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'Subscriber',
            'model_id' => $subscriber->id,
            'old_values' => $oldValues,
            'new_values' => $subscriber->toArray(),
        ]);

        return redirect()->route('subscribers.index')->with('success', 'Subscriber updated successfully.');
    }

    public function destroy(Subscriber $subscriber)
    {
        $oldValues = $subscriber->toArray();

        $subscriber->delete();

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => 'Subscriber',
            'model_id' => $subscriber->id,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('subscribers.index')->with('success', 'Subscriber deleted successfully.');
    }

    // Public-facing: subscribe via homepage/footer
    public function publicStore(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers',
            'name' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['email', 'name']);
        $data['is_active'] = true;
        $data['subscribed_at'] = now();

        Subscriber::create($data);

        return back()->with('success', 'Subscription successful.');
    }
}
