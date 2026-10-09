<?php

namespace App\Console\Commands;

use App\Mail\NewWishNotification;
use App\Mail\WishConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;

class SendTestMails extends Command
{
	protected $signature = 'mail:test
		{to? : Recipient; defaults to MAIL_NOTIFY}
		{--only= : Send just "notification" or "confirmation"}
		{--entry= : Wish entry ID; defaults to the newest wish}
		{--fake : Use made-up wish data instead of a stored entry}';

	protected $description = 'Send the wish notification and confirmation mails without submitting the form';

	public function handle(): int
	{
		$to = $this->argument('to') ?? config('mail.notify') ?? config('mail.to');

		if (!$to) {
			$this->error('No recipient: pass one or set MAIL_NOTIFY.');

			return self::FAILURE;
		}

		$only = $this->option('only');

		if ($only && !in_array($only, ['notification', 'confirmation'], true)) {
			$this->error('--only takes "notification" or "confirmation".');

			return self::FAILURE;
		}

		if (!$entry = $this->entry()) {
			$this->error('No wish entry found. Use --fake.');

			return self::FAILURE;
		}

		$mails = array_filter([
			'notification' => $only !== 'confirmation' ? new NewWishNotification($entry) : null,
			'confirmation' => $only !== 'notification' ? new WishConfirmation($entry) : null,
		]);

		// sendNow, not send: the mailables are queued, and a test should not wait
		// on the cron.
		foreach ($mails as $name => $mail) {
			Mail::to($to)->sendNow($mail);
			$this->info("Sent the {$name} to {$to}.");
		}

		return self::SUCCESS;
	}

	private function entry(): ?Entry
	{
		if ($this->option('fake')) {
			return Entries::make()
				->collection('wishes')
				->id('test')
				->data([
					'title' => 'Erika Muster',
					'email' => 'erika.muster@example.ch',
					'wish' => "Ich wünsche mir eine Bank unter dem Lindenbaum im Quartier.\nDort könnten sich alle treffen.",
				]);
		}

		if ($id = $this->option('entry')) {
			return Entries::find($id);
		}

		return Entries::query()
			->where('collection', 'wishes')
			->orderBy('submitted_at', 'desc')
			->first();
	}
}
