<?php

namespace Semantyca\Component\SemantycaNM\Site\Model;

defined('_JEXEC') or die;

use JLog;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class ArticleModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'title', 'introtext', 'alias')))->from($db->quoteName('#__content'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'com_semantycanm');

			return null;
		}
	}

}
