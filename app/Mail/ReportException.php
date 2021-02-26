<?php

namespace App\Mail;

use Error;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportException extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The error that was thrown.
     *
     * @var \Error
     */
    protected $error;

    /**
     * Create a new message instance.
     *
     * @param  \Error  $error
     * @return void
     */
    public function __construct(Error $error)
    {
        $this->error = $error;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'error' => $this->error->getMessage(),
            'file' => $this->error->getFile(),
            'line' => $this->error->getLine(),
        ];

        return $this->subject(config('app.name').' Error')
            ->text('emails.exception', $data);
    }
}
