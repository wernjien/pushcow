<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ReportException extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The exception that was thrown.
     *
     * @var Throwable
     */
    protected $throwable;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Throwable $throwable)
    {
        $this->exception = $throwable;
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
