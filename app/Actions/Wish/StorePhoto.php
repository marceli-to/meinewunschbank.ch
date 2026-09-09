<?php

namespace App\Actions\Wish;

use Illuminate\Http\UploadedFile;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;

/**
 * Puts a submitted photo into the private `wishes` container.
 *
 * Private on purpose: the visitor's consent covers publication *if the wish is
 * granted*, not immediate publication, so nothing here is web-reachable — the
 * only way in is the CP-authenticated route. The file is named after its entry,
 * so the two can always be paired up; the slug's own random suffix is what keeps
 * the path from being guessable from the submitter's name.
 */
class StorePhoto
{
	public function handle(UploadedFile $photo, string $slug): Asset
	{
		$path = now()->format('Y/m') . '/' . $slug . '.' . strtolower($photo->getClientOriginalExtension() ?: $photo->guessExtension());
		$asset = Assets::make()->container('wishes')->path($path);
		$asset->upload($photo);

		return $asset;
	}
}
