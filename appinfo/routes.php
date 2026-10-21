<?php

declare(strict_types=1);

/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

return [
	'routes' => [
		// AdminSettingsController
		[
			'name' => 'admin_settings#set',
			'url' => '/settings/admin',
			'verb' => 'POST'
		],
		// CssController
		[
			'name' => 'css#login',
			'url' => '/css/login.css',
			'verb' => 'GET'
		],
		[
			'name' => 'css#dashboard',
			'url' => '/css/dashboard.css',
			'verb' => 'GET'
		],
		// ImageController
		[
			'name' => 'image#get',
			'url' => '/image',
			'verb' => 'GET'
		],
		[
			'name' => 'image#metadata',
			'url' => '/metadata',
			'verb' => 'GET'
		],
	],
];
