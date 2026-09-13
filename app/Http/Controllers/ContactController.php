<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'firstName' => ['required', 'string', 'max:80', 'min:3'],
            'lastName' => ['required', 'string', 'max:80', 'min:3'],
            'email' => ['required', 'email', 'max:255'],
            'phoneNumber' => ['required', 'string', 'max:30', 'min:9'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        Mail::to(config('mail.contact_to'))->send(
            new ContactMail(
                firstName: $validated['firstName'],
                lastName: $validated['lastName'],
                email: $validated['email'],
                phoneNumber: $validated['phoneNumber'],
                contactMessage: $validated['message'],
            )
        );

        return back();
    }
}
