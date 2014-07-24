<?php
/**
 * 
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;
class Path {

const DATABASE		= "DATABASE";
const PAGE			= "PAGE";
const PAGECODE		= "PAGECODE";
const PAGETOOL		= "PAGETOOL";
const PAGEVIEW		= "PAGEVIEW";
const PUBLICFILES	= "PUBLICFILES";
const ROOT			= "ROOT";
const SCRIPT		= "SCRIPT";
const API			= "API";
const APICODE		= "APICODE";
const APITOOL		= "APITOOL";
const APIVIEW		= "APIVIEW";
const SRC			= "SRC";
const STYLE			= "STYLE";
const WWW			= "WWW";
const GTROOT		= "GTROOT";

public static function get($name) {

	switch($name) {
	case self::DATABASE:
		return self::get(self::SRC) . "/Database";
		break;

	case self::PAGE:
		return self::get(self::SRC) . "/Page";
		break;

	case self::PAGECODE:
		return self::get(self::PAGE) . "/Code";
		break;

	case self::PAGETOOL:
		return self::get(self::PAGE) . "/Tool";
		break;

	case self::PAGEVIEW:
		return self::get(self::PAGE) . "/View";
		break;

	case self::PUBLICFILES:
		return self::get(self::SRC) . "/PublicFiles";
		break;

	case self::ROOT:
		return dirname($_SERVER["DOCUMENT_ROOT"]);
		break;

	case self::SCRIPT:
		return self::get(self::SRC) . "/Script";
		break;

	case self::API:
		return self::get(self::SRC) . "/API";
		break;

	case self::APICODE:
		return self::get(self::API) . "/Code";
		break;

	case self::APITOOL:
		return self::get(self::API) . "/Tool";
		break;

	case self::APIVIEW:
		return self::get(self::API) . "/View";
		break;

	case self::SRC:
		return self::get(self::ROOT) . "/src";
		break;

	case self::STYLE:
		return self::get(self::SRC) . "/Style";
		break;

	case self::WWW:
		return self::get(self::ROOT) . "/www";
		break;

	case self::GTROOT:
		return realpath(__DIR__ . "/../../");

	default:
		throw new \UnexpectedValueException("Invalid path: $name");
		break;
	}
}

}#