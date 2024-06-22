<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

defined('_JEXEC') or die;

class RuntimeUtil
{
	public static function getDynamicScriptUrls($type): array
	{
		$relativeDirectory = "/components/com_semantycanm/assets/bundle";
		$directory = JPATH_ADMINISTRATOR . $relativeDirectory;
		$filesList = [];

		if (!file_exists($directory) || !is_dir($directory))
		{
			return $filesList;
		}

		$files = scandir($directory);
		foreach ($files as $file)
		{
			if (pathinfo($file, PATHINFO_EXTENSION) === $type)
			{
				$filesList[] = "bundle/" . $file;
			}
		}

		return $filesList;
	}
}


