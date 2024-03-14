<?php

namespace App\Mail;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportException extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The exception that was thrown.
     *
     * @var Throwable
     */
    protected $exception;

    /**
     * Create a new message instance.
     *
     * @param  Throwable  $exception
     * @return void
     */
    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'error' => $this->exception->getMessage(),
            'file' => $this->exception->getFile(),
            'line' => $this->exception->getLine(),
        ];

        return $this->subject(config('app.name').' Error')
            ->text('emails.exception', $data);
    }
}
