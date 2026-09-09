<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Statamic\Facades\Entry;

/**
 * Renders /sitemap.xml from the routable Statamic entries. Stays in sync with
 * the content automatically: as soon as a page is published it shows up here on
 * the next request.
 *
 * Only `pages` is a routable front-end collection — `wishes` has no `route:`
 * (submissions, not pages) and never appears here. Filtered down to published,
 * publicly visible and actually URL-addressable entries.
 *
 * Absolute URLs come from the site URL (config('app.url')), so the output is
 * correct per environment without hard-coding the production domain.
 */
class SitemapController extends Controller
{
	/** Collections with a `route:` — everything else is not a page. */
	private const COLLECTIONS = ['pages'];

	public function __invoke(): Response
	{
		$urls = Entry::query()
			->whereIn('collection', self::COLLECTIONS)
			->where('published', true)
			->get()
			->reject(fn ($entry) => $entry->private())
			->reject(fn ($entry) => (bool) $entry->value('noindex'))
			->filter(fn ($entry) => $entry->url())
			->map(fn ($entry) => [
				'loc' => $entry->absoluteUrl(),
				'lastmod' => optional($entry->lastModified())->toAtomString(),
			])
			->sortBy('loc')
			->values();

		return response()
			->view('sitemap', ['urls' => $urls])
			->header('Content-Type', 'application/xml');
	}
}
