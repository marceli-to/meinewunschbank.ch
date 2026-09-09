<?php

use App\Http\Controllers\SubmitWishController;
use Illuminate\Support\Facades\Route;

// Wish submission from the Vue form. Creates an unpublished entry in the
// `wishes` collection, stores the photo on the private `wishes` disk and
// notifies MAIL_NOTIFY. Public endpoint, so it is rate limited.
Route::post('/wishes', SubmitWishController::class)->middleware('throttle:10,1');
