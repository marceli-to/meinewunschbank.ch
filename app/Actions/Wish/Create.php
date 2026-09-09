<?php

namespace App\Actions\Wish;

use Illuminate\Support\Carbon;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;

/**
 * Writes a submission into the `wishes` collection.
 *
 * Submissions start at status `pending`; a moderator moves them to granted or
 * declined. The slug comes from MakeSlug, so the entry and its photo carry the
 * same name.
 */
class Create
{
	/**
	 * @param  array<string, mixed>  $data  the validated payload
	 */
	public function handle(array $data, Asset $photo, string $slug): Entry
	{
		$entry = Entries::make()
			->collection('wishes')
			// Publishing is inert — the collection has no route, so an entry
			// renders nowhere either way. Marking submissions published keeps the
			// CP from badging every one of them "Draft"; whether a wish was
			// granted is `status`.
			->published(true)
			->slug($slug)
			->data([
				'title' => $data['firstname'].' '.$data['lastname'],
				'status' => 'pending',
				'photo' => $photo->path(),
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

		return $entry;
	}
}
