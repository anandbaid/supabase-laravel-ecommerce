<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Silently succeed on a duplicate email instead of showing a
        // validation error — re-subscribing shouldn't feel like a mistake.
        Subscriber::firstOrCreate(['email' => $data['email']]);

        return back()->with('success', "You're subscribed! Thanks for joining.");
    }
}
