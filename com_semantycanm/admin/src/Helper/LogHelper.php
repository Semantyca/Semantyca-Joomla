<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Exception;

class LogHelper
{
	public static function logError(Exception $e, $componentName)
	{
		$logData = [
			'timestamp' => date('Y-m-d H:i:s'),
			'component' => $componentName,
			'line'      => $e->getLine(),
			'message'   => $e->getMessage(),
		];

		$date        = date('Y-m-d');
		$logFileName = $componentName . "_error_{$date}.log";
		$logFilePath = JPATH_ADMINISTRATOR . '/logs/' . $logFileName;

		error_log(json_encode($logData) . PHP_EOL, 3, $logFilePath);
	}
}
