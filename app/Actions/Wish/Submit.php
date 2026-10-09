<?php

namespace App\Actions\Wish;

use Illuminate\Http\UploadedFile;
use Statamic\Contracts\Entries\Entry;
use Throwable;

/**
 * The wish submission, start to finish: store the photo, write the entry, tell
 * the team, thank the visitor. Each step is its own action; this one only
 * orders them and owns the one thing none of them can — undoing a stored photo
 * when the entry it was meant for never came into existence.
 */
class Submit
{
	public function __construct(
		private MakeSlug $makeSlug,
		private StorePhoto $storePhoto,
		private Create $createEntry,
		private Notify $notify,
		private Confirm $confirm,
	) {}

	/**
	 * @param  array<string, mixed>  $data  the validated payload
	 */
	public function handle(array $data, UploadedFile $photo): Entry
	{
		// Worked out first: the entry and its photo are named from the same slug.
		$slug = $this->makeSlug->handle($data);

		$asset = $this->storePhoto->handle($photo, $slug);

		try {
			$entry = $this->createEntry->handle($data, $asset, $slug);
		} catch (Throwable $e) {
			// Otherwise the upload is stranded on the private disk with nothing
			// referencing it.
			$asset->delete();

			throw $e;
		}

		$this->notify->handle($entry);
		$this->confirm->handle($entry);

		return $entry;
	}
}
