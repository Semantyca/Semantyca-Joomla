<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class UserGroupModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'title')))
				->from($db->quoteName('#__usergroups'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');
			return null;
		}
	}
}
