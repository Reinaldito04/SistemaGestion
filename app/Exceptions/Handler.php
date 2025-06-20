<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
        $errors = method_exists($exception, 'errors') ? $exception->errors() : [];

        $e = is_object($exception) ? get_class($exception) : null;

        $file = method_exists($exception, 'getFile') ? $exception->getFile() : null;

        $line = method_exists($exception, 'getLine') ? $exception->getLine() : null;

        $message = (method_exists($exception, 'getMessage') && $exception->getMessage() !== '')
            ? $exception->getMessage()
            : null;

        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : ( property_exists($exception, 'status') ? $exception->status : 500 );

        $statusCode = ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
            ? 404
            : $statusCode;

        return response()->json([
            'errors'        => $errors,
            'exception'     => $e,
            'file'          => $file,
            'line'          => $line,
            'message'       => $message,
            'statusCode'    => $statusCode,
        ], $statusCode);
    }

}


