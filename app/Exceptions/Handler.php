<?php

namespace App\Exceptions;

use Mail;
use Throwable;
use App\Support\Response;
use App\Mail\ReportException;
use App\Exceptions\ClientError;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);

        if ($this->shouldReport($exception)) {
            $email = config('app.maintainer_email');

            Mail::to($email)->send(new ReportException($exception));
        }
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
        if ($exception instanceof ClientError) {
            return $exception->render();
        }

        if (config('app.debug') === true) {
            $data = [
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        return Response::error(
            $exception->getMessage(),
            $exception->getCode(),
            $data ?? null
        );
    }
}
