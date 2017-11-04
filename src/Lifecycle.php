<?php
namespace Gt\WebEngine;

use Gt\Http\RequestFactory;
use Gt\WebEngine\Dispatch\DispatcherFactory;

class Lifecycle {
	public static function start() {
		$request = RequestFactory::create($_SERVER);
		$dispatcher = DispatcherFactory::create($request);
		// TODO: Input should be an object representing any user input (GET, POST, PUT, FILES, etc).
		$dispatcher->go($request, $input);
	}

	public static function finish() {

	}
}