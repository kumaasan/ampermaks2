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
        $validated = $request->validate(
            [
                'firstName' => ['required', 'string', 'max:80', 'min:3'],
                'lastName' => ['required', 'string', 'max:80', 'min:3'],
                'email' => ['required', 'email', 'max:255'],
                'phoneNumber' => ['required', 'string', 'max:30', 'min:9'],
                'message' => ['required', 'string', 'min:10', 'max:5000'],
            ],
            [
                'firstName.required' => 'Podaj imię.',
                'firstName.string' => 'Imię musi być tekstem.',
                'firstName.min' => 'Imię musi zawierać co najmniej 3 znaki.',
                'firstName.max' => 'Imię może zawierać maksymalnie 80 znaków.',

                'lastName.required' => 'Podaj nazwisko.',
                'lastName.string' => 'Nazwisko musi być tekstem.',
                'lastName.min' => 'Nazwisko musi zawierać co najmniej 3 znaki.',
                'lastName.max' => 'Nazwisko może zawierać maksymalnie 80 znaków.',

                'email.required' => 'Podaj adres e-mail.',
                'email.email' => 'Podaj poprawny adres e-mail.',
                'email.max' => 'Adres e-mail może mieć maksymalnie 255 znaków.',

                'phoneNumber.required' => 'Podaj numer telefonu.',
                'phoneNumber.string' => 'Numer telefonu musi być tekstem.',
                'phoneNumber.min' => 'Numer telefonu musi zawierać co najmniej 9 znaków.',
                'phoneNumber.max' => 'Numer telefonu może zawierać maksymalnie 30 znaków.',

                'message.required' => 'Wpisz treść wiadomości.',
                'message.string' => 'Treść wiadomości musi być tekstem.',
                'message.min' => 'Treść wiadomości musi zawierać co najmniej 10 znaków.',
                'message.max' => 'Treść wiadomości może zawierać maksymalnie 5000 znaków.',
            ],
        );

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
