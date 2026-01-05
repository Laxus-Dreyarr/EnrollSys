<?php

namespace App\Mail;

use App\Models\Csv;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QualificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $enrollmentDeadline;

    /**
     * Create a new message instance.
     */
    public function __construct(Csv $student)
    {
        $this->student = $student;
        $this->enrollmentDeadline = now()->addDays(30)->format('F j, Y');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! You Qualify for Freshman Enrollment',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.qualification',
            with: [
                'student' => $this->student,
                'deadline' => $this->enrollmentDeadline,
            ],
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
}