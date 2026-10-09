<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Checks a Cloudflare Turnstile token with siteverify. Fails closed: a token
 * Cloudflare cannot vouch for, for whatever reason, does not get through. The
 * action and hostname checks stop a token minted elsewhere from being replayed
 * here.
 */
class Turnstile implements ValidationRule
{
	public function __construct(private string $action) {}

	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		$hostnames = config('services.turnstile.hostnames');

		if (!is_string($value) || $value === '' || strlen($value) > 2048 || $hostnames === []) {
			$fail($this->message());

			return;
		}

		try {
			$response = Http::asForm()
				->timeout(10)
				->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
					'secret' => config('services.turnstile.secret_key'),
					'response' => $value,
					'remoteip' => request()->ip(),
				]);

			if (
				$response->successful()
				&& $response->json('success') === true
				&& $response->json('action') === $this->action
				&& in_array($response->json('hostname'), $hostnames, true)
			) {
				return;
			}

			Log::notice('Turnstile rejected a wish submission.', [
				'errors' => $response->json('error-codes'),
				'action' => $response->json('action'),
				'hostname' => $response->json('hostname'),
			]);
		} catch (Throwable $e) {
			Log::warning('Turnstile could not be reached.', ['exception' => $e]);
		}

		$fail($this->message());
	}

	private function message(): string
	{
		return 'Die Spam-Prüfung ist fehlgeschlagen. Bitte laden Sie die Seite neu und versuchen Sie es noch einmal.';
	}
}
