<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
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
