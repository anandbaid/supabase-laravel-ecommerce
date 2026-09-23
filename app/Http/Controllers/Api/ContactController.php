<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Newsletter sign-up and the contact form. */
class ContactController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255']);

        // Re-subscribing shouldn't feel like a mistake, so duplicates succeed quietly.
        Subscriber::firstOrCreate(['email' => $data['email']]);

        return response()->json(['message' => "You're subscribed! Thanks for joining."]);
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:5000',
        ]);

        // Same as the Blade contact form: logged until a mailbox is wired up.
        Log::info('Contact form submission', $data);

        return response()->json(['message' => "Thanks {$data['name']}! We've received your message and will get back to you soon."]);
    }
}
