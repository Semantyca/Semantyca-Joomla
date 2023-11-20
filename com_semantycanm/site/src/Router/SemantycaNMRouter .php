<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Router;

class SemantycaNMRouter extends Router
{
	public function build(&$query)
	{
		$segments = [];

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		return $segments;
	}

	public function parse(&$segments)
	{
		$vars = [];

		$task = array_shift($segments);
		if ($task)
		{
			$vars['task'] = $task;
		}

		return $vars;
	}
}
