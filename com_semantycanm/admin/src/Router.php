<?php

namespace Semantyca\Component\SemantycaNM\Administrator;

use Joomla\CMS\Component\Router\RouterInterface;

class Router implements RouterInterface {
	public function build(&$query) {
		// Logic to convert query to SEF URL
	}

	public function parse(&$segments) {
		// Logic to convert SEF URL back to query
	}

	public function preprocess($query)
	{
		// TODO: Implement preprocess() method.
	}
}
