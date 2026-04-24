<?php

namespace App\Mail;

use App\Models\MiniGameWeeklyScore;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MiniGameWinnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public MiniGameWeeklyScore $weeklyScore
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thong bao trung thuong mini game du doan'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mini-game-winner'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
