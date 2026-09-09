<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Asset;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a submitted photo from the private `wishes` disk to a Control Panel
 * user. Nothing in this container is web-reachable, so this route is the only
 * way to look at a submission — and it requires a CP session plus the
 * `view wishes entries` permission.
 */
class ServeWishPhotoController extends Controller
{
	public function __invoke(Request $request, string $path): StreamedResponse
	{
		abort_unless($request->user()?->can('view wishes entries'), 403);

		$asset = Asset::find('wishes::'.$path);

		abort_if($asset === null, 404);

		return $asset->disk()->filesystem()->response($asset->path());
	}
}
