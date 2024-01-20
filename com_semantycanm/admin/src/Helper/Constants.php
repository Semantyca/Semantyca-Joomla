<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

class Constants
{
	public const JSON_CONTENT_TYPE = 'Content-Type: application/json; charset=UTF-8';

	const COMPONENT_NAME = "com_semantycanm";
	const MESSAGING_ERROR = -1;
	const UNDEFINED = 0;
	const SENDING_ATTEMPT = 1;
	const HAS_BEEN_SENT = 2;
	const HAS_BEEN_READ = 3;

	const EVENT_TYPE_READ = 101;
	const EVENT_TYPE_UNSUBSCRIBE = 102;
	const EVENT_TYPE_CLICK = 101;

	const TMPL_HTML = 30;
	const TMPL_HEADER = 31;
	const TMPL_DYNAMIC = 32;
	const TMPL_FOOTER = 33;
	const TMPL_MAIN = 34;
	const TMPL_ENDING = 35;
	const TMPL_WRAPPER = 36;
	const TMPL_DYNAMIC_SHORT = 37;


}
