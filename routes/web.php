<?php

use App\Http\Controllers\ServeWishPhotoController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Pages are served by Statamic's `pages` collection. Statamic registers its own
// front-end and Control Panel routes automatically.

// XML sitemap for search engines, generated from the routable Statamic entries.
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Streams a submitted photo from the private disk to a logged-in CP user, so
// the moderator can see the photo on the wish entry. Uses the base `statamic.cp`
// middleware group (session + CP auth guard) — NOT `statamic.cp.authenticated`,
// whose Inertia page-sharing middleware assumes an Inertia response and breaks
// on a raw file stream.
Route::get('/'.config('statamic.cp.route').'/wishes/{path}/photo', ServeWishPhotoController::class)
	->where('path', '.*')
	->middleware('statamic.cp')
	->name('cp.wishes.photo');
