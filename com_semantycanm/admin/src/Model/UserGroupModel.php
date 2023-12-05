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

	public function getUserGroup($user_group_title)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select('a.name, b.title as group_title')
				->from($db->quoteName('#__users', 'a'))
				->join('INNER', $db->quoteName('#__user_usergroup_map', 'c') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('c.user_id'))
				->join('INNER', $db->quoteName('#__usergroups', 'b') . ' ON ' . $db->quoteName('c.group_id') . ' = ' . $db->quoteName('b.id'))
				->where($db->quoteName('b.title') . ' = ' . $db->quote($user_group_title));
			$db->setQuery($query);

			return $db->loadObjectList();

		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());

			return null;
		}
	}
}
