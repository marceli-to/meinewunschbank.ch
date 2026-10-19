<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
	public function __invoke(): Response
	{
		$lines = app()->environment('production')
			? ['User-agent: *', 'Allow: /', '', 'Sitemap: '.route('sitemap')]
			: ['User-agent: *', 'Disallow: /'];

		return response(implode("\n", $lines)."\n")
			->header('Content-Type', 'text/plain; charset=UTF-8');
	}
}
