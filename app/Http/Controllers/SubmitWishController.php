<?php

namespace App\Http\Controllers;

use App\Actions\Wish\Submit;
use App\Http\Requests\SubmitWishRequest;
use Illuminate\Http\JsonResponse;

/**
 * Public endpoint for the wish form. Validation lives in SubmitWishRequest and
 * the work in Actions\Wish\Submit, so this only wires the two together.
 */
class SubmitWishController extends Controller
{
	public function __invoke(SubmitWishRequest $request, Submit $submit): JsonResponse
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
