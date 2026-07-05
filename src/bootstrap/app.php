<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/midtrans/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Ketika user mencoba akses halaman yang tidak diizinkan di panel Filament,
        // redirect ke halaman utama panel (dashboard) dengan notifikasi error
        // daripada menampilkan halaman 403 Forbidden yang jelek/kosong.
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            $path = $request->path();

            // Deteksi panel berdasarkan URL prefix
            if (str_starts_with($path, 'pemilik')) {
                $home = '/pemilik';
            } elseif (str_starts_with($path, 'petugas')) {
                $home = '/petugas';
            } else {
                $home = '/admin';
            }

            session()->flash('filament.notification', json_encode([
                'title'  => '🔒 Akses Ditolak',
                'status' => 'danger',
                'body'   => 'Anda tidak memiliki hak akses ke halaman tersebut.',
            ]));

            return redirect($home);
        });
    })->create();
