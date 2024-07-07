<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

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
