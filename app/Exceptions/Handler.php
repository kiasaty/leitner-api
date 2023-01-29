<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        $responseCode = parent::render($request, $exception)->getStatusCode();

        $responseBody = [
            'success' => false,
            'errors' => [
                'status' => $responseCode,
                'source' => [
                    'pointer' => $request->path()
                ]
            ]
        ];
        
        if ($exception instanceof ValidationException) {
            $responseBody['errors']['detail'] = $exception->errors();
        } elseif ($responseCode == 422) {
            $responseBody['errors']['detail'] = $exception->getMessage();
        } elseif ($responseCode == 404) {
            $responseBody['errors']['detail'] = 'Not Found!';
        } elseif ($responseCode == 401 || $responseCode == 403) {
            $responseBody['errors']['detail'] = 'Unauthorized!';
        } else {
            $responseBody['errors']['detail'] = 'Internal Error!';
        }
        
        return response()->json($responseBody, $responseCode);
    }
}
