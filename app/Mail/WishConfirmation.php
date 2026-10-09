<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Entries\Entry;

/**
 * Receipt for the visitor: their wish arrived. Queued for the same reason as
 * NewWishNotification.
 */
class WishConfirmation extends Mailable implements ShouldQueue
{
	use Queueable;
	use SerializesModels;

	/** Retry a transient transport failure instead of failing permanently. */
	public int $tries = 3;

	/** @var array<int, int> seconds before each retry */
	public array $backoff = [60, 300];

	public function __construct(public Entry $entry) {}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Vielen Dank für Ihren Herzenswunsch',
		);
	}

	public function content(): Content
	{
		return new Content(
			markdown: 'mail.wish-confirmation',
			with: [
				'name' => $this->entry->value('title'),
				'wish' => $this->entry->value('wish'),
			],
		);
	}
}
