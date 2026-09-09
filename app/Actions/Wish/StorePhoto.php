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
 * year/month folder keeps the directory browsable, and the randomised filename
 * means a guessed path can never surface somebody's submission.
 */
class StorePhoto
{
	public function handle(UploadedFile $photo): Asset
	{
		$path = now()->format('Y/m').'/'
			.Str::random(24).'.'
			.strtolower($photo->getClientOriginalExtension() ?: $photo->guessExtension());

		$asset = Assets::make()->container('wishes')->path($path);
		$asset->upload($photo);

		return $asset;
	}
}
