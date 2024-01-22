<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use DirectoryIterator;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class ImageModel extends BaseDatabaseModel
{
	public function getList()
	{
		$imagesPath = JPATH_ROOT . '/images';
		$images     = [];

		if (is_dir($imagesPath))
		{
			$cleanPath = realpath($imagesPath);
			foreach (new DirectoryIterator($cleanPath) as $fileInfo)
			{
				if ($fileInfo->isFile())
				{
					$images[] = $fileInfo->getFilename();
				}
			}
		}

		return $images;
	}
}
