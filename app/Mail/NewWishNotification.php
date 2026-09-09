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
 * Heads-up for the team that a new Herzenswunsch is waiting for review. Carries
 * only the wish itself and a CP link — the submitter's contact details stay in
 * the Control Panel rather than travelling by mail.
 *
 * Queued (database) so the public /api/wishes request returns without blocking
 * on mail transport.
 */
class NewWishNotification extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;

	/** Retry a transient transport failure instead of failing permanently. */
	public int $tries = 3;

	/** @var array<int, int> seconds before each retry */
	public array $backoff = [60, 300];

	public function __construct(public Entry $entry) {}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Neuer Herzenswunsch eingereicht',
		);
	}

	public function content(): Content
	{
		return new Content(
			markdown: 'mail.new-wish-notification',
			with: [
				'name' => $this->entry->value('title'),
				'email' => $this->entry->value('email'),
				'wish' => $this->entry->value('wish'),
				'url' => $this->entry->editUrl(),
			],
		);
	}
}
