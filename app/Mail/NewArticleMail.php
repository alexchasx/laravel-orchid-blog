<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewArticleMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Article $article,
        public readonly Subscriber $subscriber,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Новая статья: ') . $this->article->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-article',
            with: [
                'article' => $this->article,
                'url' => route('articleShow', ['article' => $this->article->slug]),
                'unsubscribeUrl' => $this->subscriber->token
                    ? route('subscribe.unsubscribe', ['token' => $this->subscriber->token])
                    : null,
            ],
        );
    }
}
