<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Helper;

defined('_JEXEC') or die;

class RuntimeUtil
{
	public static function getDynamicScriptUrl($type): ?string
	{
		$relativeDirectory = "/components/com_semantycanm/assets/bundle";
		$directory         = JPATH_ADMINISTRATOR . $relativeDirectory;
		$prefix            = "bundle-";

		if (!file_exists($directory) || !is_dir($directory))
		{
			return null;
		}

		$files = scandir($directory);
		foreach ($files as $file)
		{
			if (strpos($file, $prefix) === 0 && pathinfo($file, PATHINFO_EXTENSION) === $type)
			{
				return "bundle/" . $file;
			}
		}

		return null;
	}
}
