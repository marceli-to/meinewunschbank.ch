<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitWishRequest;
use App\Mail\NewWishNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;

/**
 * Takes a submission from the Vue form and turns it into an unpublished entry
 * in the `wishes` collection.
 *
 * The photo goes onto the private `wishes` disk (outside the web root) — it is
 * only ever seen by moderators in the Control Panel, since the visitor's consent
 * covers publication *if the wish is granted*, not immediate publication.
 *
 * Entries are created unpublished on purpose: nothing a visitor submits appears
 * anywhere until somebody in the CP has looked at it.
 */
class SubmitWishController extends Controller
{
	public function __invoke(SubmitWishRequest $request): JsonResponse
	{
		$data = $request->validated();

		$asset = $this->storePhoto($request);

		$entry = Entry::make()
			->collection('wishes')
			->published(false)
			->slug(Str::slug($data['firstname'].'-'.$data['lastname'].'-'.Str::random(6)))
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

		if ($notify = config('mail.notify')) {
			Mail::to($notify)->send(new NewWishNotification($entry));
		}

		return response()->json([
			'status' => 'ok',
			'message' => 'Vielen Dank! Ihr Herzenswunsch ist bei uns eingegangen.',
		], 201);
	}

	/**
	 * Stores the upload in the private `wishes` container under a
	 * year/month folder, with a randomised filename so a guessed path can never
	 * reveal somebody's submission.
	 */
	private function storePhoto(SubmitWishRequest $request): \Statamic\Contracts\Assets\Asset
	{
		$file = $request->file('photo');

		$path = now()->format('Y/m').'/'
			.Str::random(24).'.'
			.strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());

		$asset = Asset::make()->container('wishes')->path($path);
		$asset->upload($file);

		return $asset;
	}
}
