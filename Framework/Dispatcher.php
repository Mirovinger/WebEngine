<?php
/** TODO:
* The purpose of this is to dispatch the events to the PageCode, which is
* stored in the Request, which is stored in the Response, which is passed in
* to this constructor... :S
*/
final class Dispatcher {
	public function __construct($response) {
		$response->dispatch("init");

		if(count($_POST) > 0) {
			$response->dispatch("onPost", $_POST);
		}

		$response->dispatch("main");

		$dom = new Dom($response->getBuffer());
		$response->dispatch("preRender", $dom);

		$organiser = new FileOrganiser();
		$injector  = new Injector($dom);

		// TODO: Obtain extracted elements, pass to response.
		$domElements = $dom->scrape();
		$response->dispatch("render", $dom, $domElements, $injector);

		$dom->flush();
	}
}
?>