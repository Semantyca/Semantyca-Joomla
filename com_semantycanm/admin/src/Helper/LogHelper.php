<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Exception;
use ReflectionClass;

class LogHelper
{
	public static function logError(Exception $e, $componentName)
	{
		$reflection = new ReflectionClass($componentName);
		$className  = $reflection->getShortName();
		$logData    = [
			'time' => date('d.m.y H.i'),
			'component' => $className,
			'>'      => $e->getLine(),
			'message'   => $e->getMessage(),
		];

		$date        = date('d-m-y');
		$logFileName = $componentName . "_error_{$date}.log";
		$logFilePath = JPATH_ADMINISTRATOR . '/logs/' . $logFileName;

		error_log(json_encode($logData) . PHP_EOL, 3, $logFilePath);
	}
}
