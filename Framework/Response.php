<?php
final class Response {
	private $_buffer = "";
	private $_api = null;
	private $_pageCode = null;
	private $_pageCodeCommon = null;

	public function __construct($request) {
		header("Content-Type: {$request->contentType}; charset=utf-8");
		header("Content-Type: text/html charset=utf-8");
		header("X-Powered-By: PHP.Gt Version " . VER);

		if(EXT === "json") {
			$this->_api = $request->api;
			return;
		}

		$this->_pageCode = $request->pageCode;
		$this->_pageCodeCommon = $request->pageCodeCommon;
		ob_start();

		// Buffer current PageView and optional header/footer.
		$this->bufferPageView("Header");
		if(!$this->bufferPageView()) {
			// TODO: Maybe Request and Response class needs to extend a general
			// HTTP class or something, so they can go $this->error(404) at any
			// point?
			die("TODO: Send error 404!");
		}
		$this->bufferPageView("Footer");

		$this->storeBuffer();

		return;
	}

	/**
	* Called by the dispatcher in order, the passed in parameter is the name of
	* a function on the currently-loaded PageCode.
	* @param string $name Name of the PageCode function to call.
	* @param mixed $args Zero or more parameters to pass to the named function.
	*/
	public function dispatch($name, $parameter = null) {
		// There may or may not be a PageCode or Common PageCode.
		// Build array of objects to dispatch to.
		$dispatchArray = array();
		if(!is_null($this->_pageCode)) {
			$dispatchArray[] = $this->_pageCode;
		}
		if(!is_null($this->_pageCodeCommon)) {
			$dispatchArray[] = $this->_pageCodeCommon;
		}
		if(!is_null($this->_api)) {
			$dispatchArray[] = $this->_api;
		}

		$args = func_get_args();
		array_shift($args);

		//var_dump($dispatchArray, $name, $args);die();

		$result = null;
		// Call method, if it exists, on each existant PageCode.
		foreach($dispatchArray as $dispatchTo) {
			if(method_exists($dispatchTo, $name)) {
					$result = call_user_func_array(
						array($dispatchTo, $name),
						$args
					);
			}
		}

		return $result;
	}

	/**
	 * TODO: Docs.
	 * Creates and executes all PageTools assigned by current PageCode.
	 */
	public function executePageTools($pageToolArray, $api, $dom, $template) {
		$toolPathArray = array(
			APPROOT . DS . "PageTool",
			GTROOT . DS . "PageTool"
		);
		foreach ($pageToolArray as $tool) {
			$tool = ucfirst($tool);
			$toolFile = $tool . ".tool.php";
			$toolClass = $tool . "_PageTool";
			foreach ($toolPathArray as $path) {
				if(!is_dir($path)) {
					continue;
				}
				
				if(file_exists($path . DS . $tool . DS . $toolFile)) {
					require_once($path . DS . $tool . DS . $toolFile);
				}
				else {
					continue;
				}

				if(class_exists($toolClass)) {
					new $toolClass($api, $dom, $template);
				}
				else {
					continue;
				}
			}
		}
	}

	/**
	 * TODO: Docs.
	 * Looks for <include> tags, puts them in place in the DOM.
	 */
	public function includeDom($dom) {
		$success = 0;
		$includes = $dom->getElementsByTagName("include");
		if($includes->length == 0) {
			return false;
		}

		$includesLength = $includes->length;
		for($i = 0; $i < $includesLength; $i++) {
			$inc = $includes->item(0);
			$frag = null;

			if($inc->hasAttribute("href")) {
				$href = $inc->getAttribute("href");

				$fileArray = array(
					APPROOT . DS . "PageView" . DS . DIR . DS . $href,
					APPROOT . DS . "PageView" . DS . BASEDIR . DS . $href
				);
				foreach($fileArray as $file) {
					if(file_exists($file)) {
						$html = file_get_contents($file);
						$frag = $dom->createDocumentFragment();
						$frag->appendXML($html);
						break;
					}
				}
			}

			if(is_null($frag)) {
				continue;
			}

			$inc->parentNode->replaceChild($frag, $inc);
			$success ++;
		}

		return $success;
	}

	public function getBuffer() {
		return $this->_buffer;
	}

	public function flush($clean = false) {
		echo $this->_buffer;
		if($clean) {
			$this->_buffer = "";
		}
	}

	/**
	* Simply takes what is already in the buffer and stores it to a private
	* variable. Buffer will be parsed with DOM and later flushed to the browser.
	*/
	private function storeBuffer() {
		$this->_buffer = ob_get_clean();
	}

	/**
	* Attempts to load the current requested PageView file, or an arbitary
	* non-required addition to the PAgeView, such as a header or footer file.
	* Arbitary files are prefixed with an underscore automatically.
	* @param string $fileName The file to load.
	* @return bool Whether the file was buffered successfully.
	*/
	private function bufferPageView($fileName = null) {
		$fileArray = null;

		if(is_null($fileName)) {
			// Requested file is stored in the FILE constant.

			// Request path is absolute, only one array element needed, with
			// direct reference to DIR and FILE.
			$fileArray = array(
				APPROOT . DS . "PageView" . DS . DIR . DS . FILE . ".html",
				APPROOT . DS . "PageView" . DS . BASEDIR . DS . FILE . ".html"
			);

			// TODO: Test directory-less path if no directory is supplied.
		}
		else {
			// Strip any underscores, as these are added automatically.
			$fileName = trim($fileName, "_");
			$fileName = ucfirst($fileName);

			// List of PageView locations in priority order.
			// TODO: Test order.
			$fileArray = array(
				APPROOT . DS . "PageView" 
					. DS . DIR . DS . "_{$fileName}.html",
				APPROOT . DS . "PageView" 
					. DS . FILE . DS . "_{$fileName}.html",
				APPROOT . DS . "PageView" 
					. DS . BASEDIR . DS . "_{$fileName}.html",
				APPROOT . DS . "PageView" 
					. DS . "_{$fileName}.html"
			);
		}

		// Search for the files, in priority order.
		foreach($fileArray as $file) {
			if(file_exists($file)) {
				// Once found, require the file and stop searching for others.
				// File being required is straight HTML - will be inserted into
				// the output buffer.
				require($file);
				return true;
			}
		}

		if(is_null($fileName)) {
			// At this point, there is no PageView file loaded.
			// Must look for a dynamic file.
			// DOC: Dynamic PageView files.
			if(false !== ($dynamicFileName = $this->findDynamicPageView()) ) {
				// File being required is straight HTML - will be inserted into 
				// the output buffer.
				require($dynamicFileName);
				return true;
			}
		}

		return false;
	}

	/**
	* Attempts to find the path of a PageView's dynamic file from the current
	* request. A dynamic file is named "_Dynamic.html", and the presence of
	* this file in a directory means that a PageView doesn't have to exist - 
	* a common dynamic file can be loaded instead, which can be manupulated by
	* the page code to act as a unique PageView.
	*/
	private function findDynamicPageView() {
		$found = false;
		$lookPath = DIR . DS . FILE;
		while($found === false) {
			// Find position of last slash in requested page.
			$lastSlash = strrpos($lookPath, DS);
			$dynamicFile = APPROOT . DS . "PageView" . DS 
			. $lookPath . DS . "_Dynamic.html";

			// If found, stop looking.
			if(file_exists($dynamicFile)) {
				$found = $dynamicFile;
				break;
			}

			// Move up one directory closer to APPROOT and continue looking.
			$lookPath = substr($lookPath, 0, $lastSlash);

			// Cancel search when root found.
			if($lastSlash === false) {
				break;
			}
		}
		return $found;
	}
}
?>