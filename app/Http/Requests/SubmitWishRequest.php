<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the public wish form. Mirrors the required-field markers in
 * the design: photo, wish text, contact block and both consent checkboxes are
 * mandatory; the supplementary link is optional.
 */
class SubmitWishRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'photo' => ['required', 'image', 'mimes:jpeg,png,webp,heic', 'max:12288'],
			'wish' => ['required', 'string', 'min:20', 'max:4000'],
			'link' => ['nullable', 'string', 'max:500', 'url'],
			'firstname' => ['required', 'string', 'max:100'],
			'lastname' => ['required', 'string', 'max:100'],
			'street' => ['required', 'string', 'max:200'],
			'city' => ['required', 'string', 'max:200'],
			'email' => ['required', 'email:rfc', 'max:200'],
			'phone' => ['required', 'string', 'max:50'],
			'birthdate' => ['required', 'date', 'before:-18 years'],
			'accepts_terms' => ['accepted'],
			'accepts_publication' => ['accepted'],
		];
	}

	public function messages(): array
	{
		return [
			'photo.required' => 'Bitte laden Sie ein Foto hoch.',
			'photo.image' => 'Die Datei muss ein Bild sein (JPG, PNG, WEBP oder HEIC).',
			'photo.max' => 'Das Foto darf höchstens 12 MB gross sein.',
			'wish.required' => 'Bitte beschreiben Sie Ihren Herzenswunsch.',
			'wish.min' => 'Bitte beschreiben Sie Ihren Wunsch etwas ausführlicher.',
			'link.url' => 'Bitte geben Sie einen gültigen Link an (inkl. https://).',
			'firstname.required' => 'Bitte geben Sie Ihren Vornamen an.',
			'lastname.required' => 'Bitte geben Sie Ihren Nachnamen an.',
			'street.required' => 'Bitte geben Sie Strasse und Nummer an.',
			'city.required' => 'Bitte geben Sie PLZ und Ort an.',
			'email.required' => 'Bitte geben Sie Ihre E-Mail-Adresse an.',
			'email.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse an.',
			'phone.required' => 'Bitte geben Sie Ihre Telefonnummer an.',
			'birthdate.required' => 'Bitte geben Sie Ihr Geburtsdatum an.',
			'birthdate.before' => 'Herzenswünsche können von Personen ab 18 Jahren eingereicht werden.',
			'accepts_terms.accepted' => 'Bitte akzeptieren Sie die Teilnahmebedingungen.',
			'accepts_publication.accepted' => 'Bitte stimmen Sie der Veröffentlichung zu.',
		];
	}
}
