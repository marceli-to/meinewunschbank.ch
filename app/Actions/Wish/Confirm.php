<?php

namespace App\Actions\Wish;

use App\Mail\WishConfirmation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Entries\Entry;
use Throwable;

/**
 * Sends the visitor a receipt for their wish. Like Notify, a queue that refuses
 * the job is logged rather than surfaced — the wish is already saved.
 */
class Confirm
{
	public function handle(Entry $entry): void
	{
		try {
			Mail::to($entry->value('email'), $entry->value('title'))->send(new WishConfirmation($entry));
		} catch (Throwable $e) {
			Log::error('Wish confirmation could not be queued.', [
				'entry' => $entry->id(),
				'exception' => $e,
			]);
		}
	}
}
