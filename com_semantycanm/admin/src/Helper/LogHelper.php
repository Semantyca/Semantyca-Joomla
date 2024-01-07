<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

use Exception;
use ReflectionClass;

class LogHelper
{
	const log_pattern = "%s: %s, Component: %s, Message: %s";
	const INF = "INFFFFO";
	const WARN = "WWWWARN";
	const ERR = "ERRRRR";

	public static function logInfo(string $msg, $componentName)
	{
		$reflection  = new ReflectionClass($componentName);
		$className   = $reflection->getShortName();
		$logEntry = sprintf(
			self::log_pattern,
			INF,
			date('d.m.y H.i'),
			$className,
			$msg
		);
		$logFilePath = JPATH_ADMINISTRATOR . '/logs/' . self::getFileName($className, self::INF);
		error_log($logEntry . PHP_EOL, 3, $logFilePath);
	}

	public static function logWarn(string $msg, $componentName)
	{
		$reflection  = new ReflectionClass($componentName);
		$className   = $reflection->getShortName();
		$logEntry = sprintf(
			self::log_pattern,
			self::WARN,
			date('d.m.y H.i'),
			$className,
			$msg
		);

		$logFilePath = JPATH_ADMINISTRATOR . '/logs/' . self::getFileName($className, self::WARN);
		error_log($logEntry . PHP_EOL, 3, $logFilePath);
	}


	public static function logException(Exception $e, $componentName)
	{
		$reflection = new ReflectionClass($componentName);
		$className  = $reflection->getShortName();
		$logEntry   = sprintf(
			"%s: %s, Component: %s, Line: %d, Message: %s\nStack Trace:\n%s",
			self::ERR,
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
					$formattedLines[] = "";
				}
			}
			$formattedLogEntry = implode("\n", $formattedLines);
		}

		error_log('----------------------------------------------' . PHP_EOL . $formattedLogEntry . PHP_EOL, 3, $logFilePath);
	}


	public static function getFileName($className, $type)
	{
		$date = date('d-m-y');

		return 'semantyca_' . $type . "_{$date}.log";
	}
}
