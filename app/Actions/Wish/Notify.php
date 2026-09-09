<?php

namespace App\Actions\Wish;

use App\Mail\NewWishNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Entries\Entry;
use Throwable;

/**
 * Tells the team a wish is waiting for review.
 *
 * The wish is already saved by the time this runs, so a queue that refuses the
 * job must not turn a successful submission into an error for the visitor — it
 * is logged and swallowed. Delivery itself is the worker's problem; the mailable
 * is queued.
 */
class Notify
{
	public function handle(Entry $entry): void
	{
		if (!$notify = config('mail.notify')) {
			return;
		}

		try {
			Mail::to($notify)->send(new NewWishNotification($entry));
		} catch (Throwable $e) {
			Log::error('Wish notification could not be queued.', [
				'entry' => $entry->id(),
				'exception' => $e,
			]);
		}
	}
}
