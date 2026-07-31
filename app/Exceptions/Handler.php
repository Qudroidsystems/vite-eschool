<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle 419 TokenMismatchException - Redirect to login
        if ($exception instanceof TokenMismatchException) {
            // Store the URL they were trying to access (Method 5)
            $intendedUrl = $request->fullUrl();

            // For AJAX/API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired. Please login again.',
                    'redirect' => route('login')
                ], 419);
            }

            // Clear old session data (Method 4)
            $request->session()->flush();

            // Check if user was already logged in
            if (auth()->check()) {
                auth()->logout();
            }

            // Redirect to login with both error and intended URL (Method 4 + 5)
            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.')
                ->with('intended', $intendedUrl)
                ->withInput();
        }

        return parent::render($request, $exception);
    }
}
