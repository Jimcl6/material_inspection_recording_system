<?php

namespace App\Http\Middleware;

use App\Services\QrCodeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class QrCodeAuth
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') && $request->has('qr_data')) {
            $qrData = $request->get('qr_data');
            $user = $this->qrCodeService->findUserByQrData($qrData);

            if ($user && $user->isActive()) {
                // Log the user in
                Auth::login($user, true);
                
                // Record the login
                $user->recordLogin(
                    $request->ip(),
                    $request->userAgent(),
                    'qr_code'
                );

                return redirect()->intended(route('dashboard'));
            }
        }

        return $next($request);
    }
}
