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
			$db    = $this->getDatabase();
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

	public function add($user_group_model, $mailing_list_name, $mailing_lists)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);

			$all_subscribers     = array();
			$mailing_lists_array = explode(",", $mailing_lists);
			foreach ($mailing_lists_array as $ml)
			{
				$subscribers     = $user_group_model->getUserGroup(trim(str_replace(["\r", "\n"], '', $ml)));
				$all_subscribers = array_merge($all_subscribers, $subscribers);
			}

			$all_subscribers = $this->remove_duplicate_emails($all_subscribers);

			$db->transactionStart();

			$query
				->insert($db->quoteName('#__nm_mailing_list'))
				->columns(array('name'))
				->values(array($db->quote($mailing_list_name)));
			$db->setQuery($query);
			$db->execute();

			$mailing_list_id = $db->insertid();

			foreach ($all_subscribers as $subscriber)
			{
				$query = $db->getQuery(true);
				$query
					->insert($db->quoteName('#__nm_subscribers'))
					->columns(array('name', 'email', 'mail_list_id'))
					->values($db->quote($subscriber->name) . ', ' . $db->quote($subscriber->email) . ', ' . $db->quote($mailing_list_id));

				$db->setQuery($query);
				$db->execute();
			}

			$db->transactionCommit();

			return 1;
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();
			error_log($e->getMessage());
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}

		return 0;
	}


	public function remove($ids)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('id') . ' IN (' . $ids . ')'
		);

		$query->delete($db->quoteName('#__nm_mailing_list'));
		$query->where($conditions);

		$db->setQuery($query);
		$result = $db->execute();

		if ($result)
		{
			return $db->getAffectedRows();
		}
		else
		{
			Log::add("The mail list deletion was failed", Log::WARNING, Constants::COMPONENT_NAME);

			return 0;
		}
	}

	function remove_duplicate_emails($subscribers): array
	{
		$emails = array_map(function ($subscriber) {
			return $subscriber->email;
		}, $subscribers);

		$unique_emails = array_unique($emails);

		return array_values(array_intersect_key($subscribers, $unique_emails));
	}


}
