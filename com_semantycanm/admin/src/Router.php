<?php

namespace Semantyca\Component\SemantycaNM\Administrator;

use Joomla\CMS\Component\Router\RouterInterface;

class Router implements RouterInterface {
	public function build(&$query) {
		error_log($query);
	}

	public function parse(&$segments) {
		error_log($segments);
	}

	public function preprocess($query)
	{
		error_log($query);
	}
}
