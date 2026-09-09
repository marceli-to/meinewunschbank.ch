<?php

namespace App\Actions\Wish;

use Illuminate\Support\Str;

/**
 * The slug identifying one submission.
 *
 * Readable from the submitter's name, with a random suffix because the slug is
 * the entry's filename — two people with the same name would otherwise overwrite
 * each other. Both the entry and its photo are named from this, so it is worked
 * out once, before either exists.
 */
class MakeSlug
{
	/**
	 * @param  array<string, mixed>  $data  the validated payload
	 */
	public function handle(array $data): string
	{
		return Str::slug($data['firstname'].'-'.$data['lastname']).'-'.Str::lower(Str::random(6));
	}
}
