<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function sendFeedback(Request $request)
    {
        $request->validate([
            'feedback' => 'required|string',
            'email' => 'required|email'
        ]);

        Mail::raw(
            "New Feedback Received\n\nFrom: {$request->email}\n\nFeedback:\n{$request->feedback}",
            function ($message) {
                $message->to(env('MAIL_TO_ADDRESS', 'admin@laverdadherald.com'))
                    ->subject('New Feedback - La Verdad Herald');
            }
        );

        return response()->json(['message' => 'Feedback received successfully'])
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    public function requestCoverage(Request $request)
    {
        $request->validate([
            'eventName' => 'required|string',
            'date' => 'required|date',
            'description' => 'required|string',
            'contactEmail' => 'required|email'
        ]);

        Mail::raw(
            "New Coverage Request\n\nEvent: {$request->eventName}\nDate: {$request->date}\nContact: {$request->contactEmail}\n\nDescription:\n{$request->description}",
            function ($message) {
                $message->to(env('MAIL_TO_ADDRESS', 'admin@laverdadherald.com'))
                    ->subject('Coverage Request - La Verdad Herald');
            }
        );

        return response()->json(['message' => 'Coverage request received successfully'])
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    public function joinHerald(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'course' => 'required|string',
            'gender' => 'required|string',
            'pubName' => 'required|string',
            'specificPosition' => 'required|string'
        ]);

        $classifications = json_encode($request->classifications ?? []);
        $pubOption = json_encode($request->pubOption ?? []);
        $designations = json_encode($request->designations ?? []);

        $emailBody = "New Membership Application\n\n";
        $emailBody .= "Personal Information:\n";
        $emailBody .= "Name: {$request->name}\n";
        $emailBody .= "Course & Year: {$request->course}\n";
        $emailBody .= "Gender: {$request->gender}\n\n";
        $emailBody .= "Publication Information:\n";
        $emailBody .= "Publication Name: {$request->pubName}\n";
        $emailBody .= "Classifications: {$classifications}\n";
        $emailBody .= "Publishing Option: {$pubOption}\n";
        $emailBody .= "Designations: {$designations}\n";
        $emailBody .= "Specific Position: {$request->specificPosition}\n";

        Mail::raw($emailBody, function ($message) {
            $message->to(env('MAIL_TO_ADDRESS', 'admin@laverdadherald.com'))
                ->subject('Membership Application - La Verdad Herald');
        });

        return response()->json(['message' => 'Application submitted successfully'])
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        Mail::raw(
            "New Newsletter Subscription\n\nEmail: {$request->email}",
            function ($message) {
                $message->to(env('MAIL_TO_ADDRESS', 'admin@laverdadherald.com'))
                    ->subject('Newsletter Subscription - La Verdad Herald');
            }
        );

        return response()->json(['message' => 'Subscription successful'])
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
}
