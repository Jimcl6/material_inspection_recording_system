<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    private const SUPPORT_MESSAGE = 'Password reset emails are not available. Please contact an administrator to reset your password.';

    /**
     * Display the password recovery support view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status') ?? self::SUPPORT_MESSAGE,
        ]);
    }

    /**
     * Keep the legacy POST endpoint harmless while email reset is disabled.
     */
    public function store(): RedirectResponse
    {
        return back()->with('status', self::SUPPORT_MESSAGE);
    }
}
