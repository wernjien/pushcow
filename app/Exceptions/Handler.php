<?php

namespace App\Exceptions;

use App\Mail\ReportException;
use App\Support\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Mail;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function report(Throwable $throwable)
    {
        parent::report($throwable);

        if (App::environment() == 'production' && $this->shouldReport($throwable)) {
            $email = config('app.maintainer_email');

            if (! empty($email)) {
                Mail::to($email)->send(new ReportException($throwable));
            }
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $throwable)
    {
        if ($throwable instanceof ClientError) {
            return $throwable->render();
        }

        if ($throwable instanceof ValidationException) {
            return Response::fail($throwable->errors(), $throwable->status);
        }

        if ($throwable instanceof AuthenticationException) {
            return Response::error($throwable->getMessage(), null, null, 401);
        }

        $throwable = $this->prepareException($throwable);

        $status = $throwable instanceof HttpExceptionInterface
            ? $throwable->getStatusCode()
            : 500;

        $headers = $throwable instanceof HttpExceptionInterface
            ? $throwable->getHeaders()
            : [];

        if (config('app.debug') === true) {
            $data = [
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTrace(),
            ];
        }

        return Response::error(
            $throwable->getMessage() ?: 'Server Error',
            $throwable->getCode(),
            $data ?? null,
            $status,
            $headers
        );
    }
}
