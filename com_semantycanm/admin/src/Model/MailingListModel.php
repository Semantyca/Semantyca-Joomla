<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class MailingListModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select(array($db->quoteName('#__nm_mailing_list.name'), $db->quoteName('#__nm_mailing_list.id')))
				->from($db->quoteName('#__nm_mailing_list'))
				->join('LEFT',
					$db->quoteName('#__nm_subscribers') . ' ON (' . $db->quoteName('#__nm_mailing_list.id') . ' = ' . $db->quoteName('#__nm_subscribers.mail_list_id') . ')')
				->group($db->quoteName('#__nm_mailing_list.id'));
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
			return null;
		}
	}

	public function getSubscribers($mailing_list_name)
	{
		try
		{
			$db   = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select(array($db->quoteName('#__nm_subscribers.email')))
				->from($db->quoteName('#__nm_mailing_list'))
				->join('LEFT',
					$db->quoteName('#__nm_subscribers') . ' ON (' . $db->quoteName('#__nm_mailing_list.id') . ' = ' . $db->quoteName('#__nm_subscribers.mail_list_id') . ')')
				->where($db->quoteName('#__nm_mailing_list.name') . ' = ' . $db->quote($mailing_list_name))
				->group($db->quoteName('#__nm_mailing_list.id'));
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
			return null;
		}
	}


	public function add($name)
	{
		try
		{
			Log::add('addRecord method was triggered', Log::INFO, Constants::COMPONENT_NAME);
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);

			$columns = array('name');
			$values  = array($db->quote($name));

			$query
				->insert($db->quoteName('#__nm_mailing_list'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));

			$db->setQuery($query);
			$db->execute();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
		}
	}

	public function remove($ids)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('id') . ' IN (' . implode(',', $ids) . ')'
		);

		$query->delete($db->quoteName('#__nm_mailing_list'));
		$query->where($conditions);

		$db->setQuery($query);
		$db->execute();
	}
}
