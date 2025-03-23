<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The user name.
     *
     * @var string
     */
    public $name;

    /**
     * The reset time.
     *
     * @var \Carbon\Carbon
     */
    public $reset_time;

    /**
     * Create a new message instance.
     *
     * @param string $name
     * @param \Carbon\Carbon $reset_time
     * @return void
     */
    public function __construct(string $name, Carbon $reset_time)
    {
        $this->name = $name;
        $this->reset_time = $reset_time;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Password Berhasil Direset - UPN Veteran Jakarta Tracer Study',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password.reset-success',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Password Berhasil Direset - UPN Veteran Jakarta Tracer Study')
                    ->view('emails.password.reset-success')
                    ->with([
                        'name' => $this->name,
                        'reset_time' => $this->reset_time,
                    ]);
    }
}