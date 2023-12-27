<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class UserGroupModel extends BaseDatabaseModel
{
	public function getList()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__usergroups'));
		$db->setQuery($query);

		return $db->loadObjectList();

	}

	public function getUserGroup($user_group_title)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select('a.name, b.title as group_title, a.email')
			->from($db->quoteName('#__users', 'a'))
			->join('INNER', $db->quoteName('#__user_usergroup_map', 'c') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('c.user_id'))
			->join('INNER', $db->quoteName('#__usergroups', 'b') . ' ON ' . $db->quoteName('c.group_id') . ' = ' . $db->quoteName('b.id'))
			->where($db->quoteName('b.title') . ' = ' . $db->quote($user_group_title));
		$db->setQuery($query);

		return $db->loadObjectList();

	}
}
