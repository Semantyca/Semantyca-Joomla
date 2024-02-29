<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

class Constants
{
	public const JSON_CONTENT_TYPE = 'Content-Type: application/json; charset=UTF-8';

	const COMPONENT_NAME = "com_semantycanm";
	const EXT_SCRIPT = "sendemail.php";
	const NOT_FULFILLED = -1;
	const UNDEFINED = 0;
	const PROCESSING = 1;
	const DONE = 2;

	const EVENT_TYPE_UNKNOWN = 99;
	const EVENT_TYPE_DISPATCHED = 100;
	const EVENT_TYPE_READ = 101;
	const EVENT_TYPE_UNSUBSCRIBE = 102;
	const EVENT_TYPE_CLICK = 103;


}
