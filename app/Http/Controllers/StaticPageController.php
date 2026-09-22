<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StaticPageController extends Controller
{
    public function about()
    {
        return view('static.about');
    }

    public function privacy()
    {
        return view('static.privacy');
    }

    public function returns()
    {
        return view('static.returns');
    }

    public function contact()
    {
        return view('static.contact');
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:5000',
        ]);

        // Wire this up to a Mail::send(...) or a Contact model once you're
        // ready to persist/route these — logging keeps it simple for now.
        Log::info('Contact form submission', $data);

        return back()->with('success', "Thanks {$data['name']}! We've received your message and will get back to you soon.");
    }
}
