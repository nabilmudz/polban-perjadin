<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class HandleInertiaError
{
    public static function resolve(Throwable $e): array
    {
        if ($e instanceof ValidationException) {
            return [
                'title' => 'Validasi Gagal',
                'message' => implode("\n", $e->validator->errors()->all()),
            ];
        }

        if ($e instanceof DomainException) {
            return [
                'title' => 'Aksi Tidak Diizinkan',
                'message' => $e->getMessage(),
            ];
        }

        if ($e instanceof HttpException) {
            return match ($e->getStatusCode()) {
                403 => [
                    'title' => 'Akses Ditolak',
                    'message' => $e->getMessage() ?: 'Anda tidak memiliki akses.',
                ],
                404 => [
                    'title' => 'Data Tidak Ditemukan',
                    'message' => $e->getMessage() ?: 'Data tidak ditemukan.',
                ],
                409 => [
                    'title' => 'Konflik Data',
                    'message' => $e->getMessage() ?: 'Terjadi konflik data.',
                ],
                422 => [
                    'title' => 'Data Tidak Valid',
                    'message' => $e->getMessage() ?: 'Data yang dikirim tidak valid.',
                ],
                default => [
                    'title' => 'Terjadi Kesalahan',
                    'message' => $e->getMessage() ?: 'Terjadi kesalahan.',
                ],
            };
        }

        return [
            'title' => 'Terjadi Kesalahan',
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Terjadi kesalahan pada sistem. Silakan coba lagi.',
        ];
    }
}
