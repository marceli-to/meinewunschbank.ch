<?php

namespace App\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;

/**
 * Writes a submission into the `wishes` collection.
 *
 * Always unpublished: nothing a visitor submits appears anywhere until somebody
 * in the Control Panel has looked at it. The slug stays readable but carries a
 * random suffix, so two submissions from the same name cannot collide.
 */
class CreateWishEntry
{
	/**
	 * @param  array<string, mixed>  $data  the validated payload
	 */
	public function handle(array $data, Asset $photo): Entry
	{
		$entry = Entries::make()
			->collection('wishes')
			->published(false)
			->slug(Str::slug($data['firstname'].'-'.$data['lastname'].'-'.Str::random(6)))
			->data([
				'title' => $data['firstname'].' '.$data['lastname'],
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
