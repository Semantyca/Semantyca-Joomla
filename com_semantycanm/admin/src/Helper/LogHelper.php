<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Exception;
use ReflectionClass;

class LogHelper
{
	const INF = "inf";
	const WARN = "warn";
	const ERR = "err";

	public static function logInfo(string $msg, $componentName)
	{
		$reflection  = new ReflectionClass($componentName);
		$className   = $reflection->getShortName();
		$logData     = [
			'time'      => date('d.m.y H.i'),
			'component' => $className,
			'message'   => $msg,
		];
		$logFilePath = JPATH_ADMINISTRATOR . '/logs/' . self::getFileName($className, self::INF);
		error_log(json_encode($logData) . PHP_EOL, 3, $logFilePath);
	}

	public static function logError(string $msg, $componentName)
	{
		$reflection  = new ReflectionClass($componentName);
		$className   = $reflection->getShortName();
		$logData     = [
			'time'      => date('d.m.y H.i'),
			'component' => $className,
			'message'   => $msg,
		];
		$logFilePath = JPATH_ADMINISTRATOR . '/logs/' . self::getFileName($className, self::WARN);
		error_log(json_encode($logData) . PHP_EOL, 3, $logFilePath);
	}

	public static function logException(Exception $e, $componentName)
	{
		$reflection = new ReflectionClass($componentName);
		$className  = $reflection->getShortName();
		$logEntry   = sprintf(
			"Time: %s, Component: %s, Line: %d, Message: %s\nStack Trace:\n%s",
			date('d.m.y H.i'),
			$className,
			$e->getLine(),
			$e->getMessage(),
			$e->getTraceAsString()
		);

		$logFilePath = JPATH_ADMINISTRATOR . '/logs/' . self::getFileName($className, self::ERR);

		if ($logEntry) {
			$lines = explode("\n", $logEntry);
			$formattedLines = [];
			foreach ($lines as $line) {
				$formattedLines[] = $line;
				if (strpos($line, "SemantycaNM") !== false) {
					$formattedLines[] = "";  // Add an empty line for extra spacing
				}
			}
			$formattedLogEntry = implode("\n", $formattedLines);
		}

		error_log($formattedLogEntry . PHP_EOL, 3, $logFilePath);
	}


	public static function getFileName($className, $type)
	{
		$date = date('d-m-y');

		return 'semantyca_' . $type . "_{$date}.log";
	}
}
