<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        if (!$request->inertia()) {
            return parent::render($request, $e);
        }

        // DEFAULT
        $title = 'Terjadi Kesalahan';
        $message = 'Terjadi kesalahan pada sistem.';

        if ($e instanceof ValidationException) {
            $title = 'Validasi Gagal';
            $message = implode("\n", $e->validator->errors()->all());
        }

        if ($e instanceof HttpException) {
            $title = match ($e->getStatusCode()) {
                403 => 'Akses Ditolak',
                404 => 'Data Tidak Ditemukan',
                409 => 'Konflik Data',
                422 => 'Data Tidak Valid',
                default => 'Terjadi Kesalahan',
            };

            $message = $e->getMessage();
        }

        Inertia::share([
            'error' => [
                'title' => $title,
                'message' => $message,
            ],
        ]);

        return parent::render($request, $e);
    }
}
