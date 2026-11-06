<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'soap/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Paksa setiap rute berawalan /api/* merender respons JSON (bukan halaman HTML!)
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // 2. Standarkan Error Validasi Input (422 Unprocessable Content - RFC 7807)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type'     => url('/docs/errors/validation-failed'),
                    'title'    => 'Unprocessable Content',
                    'status'   => 422,
                    'detail'   => 'Satu atau lebih atribut input yang dikirim tidak memenuhi aturan validasi.',
                    'instance' => $request->getRequestUri(),
                    'errors'   => $e->errors(),
                ], 422)->header('Content-Type', 'application/problem+json');
            }
        });

        // 3. Standarkan Error Belum Login / Token Tidak Valid (401 Unauthorized - RFC 7807)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type'     => url('/docs/errors/unauthenticated'),
                    'title'    => 'Unauthorized',
                    'status'   => 401,
                    'detail'   => 'Akses ditolak. Sertakan Bearer Token yang valid pada header Authorization.',
                    'instance' => $request->getRequestUri(),
                ], 401)->header('Content-Type', 'application/problem+json');
            }
        });

        // 4. Standarkan Error Endpoint Salah Ketik (404 Not Found - RFC 7807)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type'     => url('/docs/errors/endpoint-not-found'),
                    'title'    => 'Not Found',
                    'status'   => 404,
                    'detail'   => 'Alamat URI endpoint atau resource yang diminta tidak ditemukan.',
                    'instance' => $request->getRequestUri(),
                ], 404)->header('Content-Type', 'application/problem+json');
            }
        });

        // 5. Standarkan Error HTTP Verb Tidak Diizinkan (405 Method Not Allowed - RFC 7807)
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type'     => url('/docs/errors/method-not-allowed'),
                    'title'    => 'Method Not Allowed',
                    'status'   => 405,
                    'detail'   => 'Metode HTTP (Verb) yang digunakan tidak diizinkan pada endpoint ini.',
                    'instance' => $request->getRequestUri(),
                ], 405)->header('Content-Type', 'application/problem+json');
            }
        });
    })->create();
