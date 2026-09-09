<?php

namespace App\Actions\Wish;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;

/**
 * Puts a submitted photo into the private `wishes` container.
 *
 * Private on purpose: the visitor's consent covers publication *if the wish is
 * granted*, not immediate publication, so nothing here is web-reachable. The
 * year/month folder keeps the directory browsable and the name ties the file to
 * its entry, while the random suffix keeps the path from being guessable from
 * the submitter's name alone.
 */
class StorePhoto
{
	public function handle(UploadedFile $photo, string $slug): Asset
	{
		$path = now()->format('Y/m').'/'
			.$slug.'-'.Str::lower(Str::random(12)).'.'
			.strtolower($photo->getClientOriginalExtension() ?: $photo->guessExtension());

		$asset = Assets::make()->container('wishes')->path($path);
		$asset->upload($photo);

		return $asset;
	}
}
