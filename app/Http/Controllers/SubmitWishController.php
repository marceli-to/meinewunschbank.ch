<?php

namespace App\Http\Controllers;

use App\Actions\SubmitWish;
use App\Http\Requests\SubmitWishRequest;
use Illuminate\Http\JsonResponse;

/**
 * Public endpoint for the wish form. Validation lives in SubmitWishRequest and
 * the work in SubmitWish, so this only wires the two together.
 */
class SubmitWishController extends Controller
{
	public function __invoke(SubmitWishRequest $request, SubmitWish $submit): JsonResponse
	{
		$submit->handle(
			data: $request->validated(),
			photo: $request->file('photo'),
		);

		return response()->json([
			'status' => 'ok',
			'message' => 'Vielen Dank! Ihr Herzenswunsch ist bei uns eingegangen.',
		], 201);
	}
}
