<?php

namespace App\Actions;

use App\Mail\NewWishNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Asset as Assets;
use Statamic\Facades\Entry as Entries;
use Throwable;

/**
 * Turns a validated submission into an unpublished entry in the `wishes`
 * collection.
 *
 * Entries are created unpublished on purpose: nothing a visitor submits appears
 * anywhere until somebody in the Control Panel has looked at it. The photo goes
 * onto the private `wishes` disk (outside the web root) — the visitor's consent
 * covers publication *if the wish is granted*, not immediate publication.
 */
class SubmitWish
{
	/**
	 * @param  array<string, mixed>  $data  the validated payload
	 */
	public function handle(array $data, UploadedFile $photo): Entry
	{
		$asset = $this->storePhoto($photo);

		try {
			$entry = Entries::make()
				->collection('wishes')
				->published(false)
				->slug($this->slugFor($data))
				->data([
					'title' => $data['firstname'].' '.$data['lastname'],
					'photo' => $asset->path(),
					'wish' => $data['wish'],
					'link' => $data['link'] ?? null,
					'firstname' => $data['firstname'],
					'lastname' => $data['lastname'],
					'street' => $data['street'],
					'city' => $data['city'],
					'email' => $data['email'],
					'phone' => $data['phone'],
					'birthdate' => Carbon::parse($data['birthdate'])->format('Y-m-d'),
					'accepts_terms' => true,
					'accepts_publication' => true,
					'submitted_at' => now()->toDateTimeString(),
				]);

			$entry->save();
		} catch (Throwable $e) {
			// Don't strand the upload if the entry never came into existence.
			$asset->delete();

			throw $e;
		}

		$this->notify($entry);

		return $entry;
	}

	/**
	 * Stores the upload in the private `wishes` container under a year/month
	 * folder, with a randomised filename so a guessed path can never reveal
	 * somebody's submission.
	 */
	private function storePhoto(UploadedFile $photo): Asset
	{
		$path = now()->format('Y/m').'/'
			.Str::random(24).'.'
			.strtolower($photo->getClientOriginalExtension() ?: $photo->guessExtension());

		$asset = Assets::make()->container('wishes')->path($path);
		$asset->upload($photo);

		return $asset;
	}

	/**
	 * A readable slug that still can't collide between two submissions from the
	 * same name.
	 */
	private function slugFor(array $data): string
	{
		return Str::slug($data['firstname'].'-'.$data['lastname'].'-'.Str::random(6));
	}

	/**
	 * The wish is already saved by the time this runs, so a queue that refuses
	 * the job must not turn a successful submission into an error for the
	 * visitor — log it and carry on.
	 */
	private function notify(Entry $entry): void
	{
		if (! $notify = config('mail.notify')) {
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
