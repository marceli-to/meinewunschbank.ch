<?php

namespace App\Actions\Wish;

use Illuminate\Http\UploadedFile;
use Statamic\Contracts\Entries\Entry;
use Throwable;

/**
 * The wish submission, start to finish: store the photo, write the entry, tell
 * the team. Each step is its own action; this one only orders them and owns the
 * one thing none of them can — undoing a stored photo when the entry it was
 * meant for never came into existence.
 */
class Submit
{
	public function __construct(
		private StorePhoto $storePhoto,
		private Create $createEntry,
		private Notify $notify,
	) {}

	/**
	 * @param  array<string, mixed>  $data  the validated payload
	 */
	public function handle(array $data, UploadedFile $photo): Entry
	{
		$asset = $this->storePhoto->handle($photo);

		try {
			$entry = $this->createEntry->handle($data, $asset);
		} catch (Throwable $e) {
			// Otherwise the upload is stranded on the private disk with nothing
			// referencing it.
			$asset->delete();

			throw $e;
		}

		$this->notify->handle($entry);

		return $entry;
	}
}
