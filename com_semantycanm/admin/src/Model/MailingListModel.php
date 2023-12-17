<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use ComSemantycanm\Admin\Exception\RecordNotFoundException;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class MailingListModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select(
				array(
					$db->quoteName('#__semantyca_nm_mailing_list.name'),
					$db->quoteName('#__semantyca_nm_mailing_list.id'),
					$db->quoteName('#__semantyca_nm_mailing_list.reg_date')))
				->from($db->quoteName('#__semantyca_nm_mailing_list'))
				->join('LEFT',
					$db->quoteName('#__semantyca_nm_subscribers') . ' ON (' . $db->quoteName('#__semantyca_nm_mailing_list.id') . ' = ' . $db->quoteName('#__semantyca_nm_subscribers.mail_list_id') . ')')
				->group($db->quoteName('#__semantyca_nm_mailing_list.id'))
				->order('reg_date DESC');

			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);

			return null;
		}
	}

	public function find($id)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select(
				array(
					$db->quoteName('#__semantyca_nm_mailing_list.name'),
					$db->quoteName('#__semantyca_nm_mailing_list.id'),
					$db->quoteName('#__semantyca_nm_mailing_list.reg_date')))
				->from($db->quoteName('#__semantyca_nm_mailing_list'))
				->join('LEFT',
					$db->quoteName('#__semantyca_nm_subscribers') . ' ON (' . $db->quoteName('#__semantyca_nm_mailing_list.id') . ' = ' . $db->quoteName('#__semantyca_nm_subscribers.mail_list_id') . ')')
				->where($db->quoteName('#__semantyca_nm_mailing_list.id') . ' = ' . $db->quote($id))
				->group($db->quoteName('#__semantyca_nm_mailing_list.id'))
				->order('reg_date DESC');

			$db->setQuery($query);
			$result = $db->loadObject();
			if (!$result)
			{
				throw new RecordNotFoundException("Record not found for ID: $id");
			}

			return $result;

		}
		catch (RecordNotFoundException|\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);

			return null;
		}
	}


	public function getSubscribers($mailing_list_name)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select(array($db->quoteName('#__semantyca_nm_subscribers.email')))
				->from($db->quoteName('#__semantyca_nm_mailing_list'))
				->join('LEFT',
					$db->quoteName('#__semantyca_nm_subscribers') . ' ON (' . $db->quoteName('#__semantyca_nm_mailing_list.id') . ' = ' . $db->quoteName('#__semantyca_nm_subscribers.mail_list_id') . ')')
				->where($db->quoteName('#__semantyca_nm_mailing_list.name') . ' = ' . $db->quote($mailing_list_name))
				->group($db->quoteName('#__semantyca_nm_mailing_list.id'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);

			return null;
		}
	}

	public function add($user_group_model, $mailing_list_name, $mailing_lists): object
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
				->insert($db->quoteName('#__semantyca_nm_mailing_list'))
				->columns(array('name'))
				->values(array($db->quote($mailing_list_name)));
			$db->setQuery($query);
			$db->execute();

			$mailing_list_id = $db->insertid();

			foreach ($all_subscribers as $subscriber)
			{
				$query = $db->getQuery(true);
				$query
					->insert($db->quoteName('#__semantyca_nm_subscribers'))
					->columns(array('name', 'email', 'mail_list_id'))
					->values($db->quote($subscriber->name) . ', ' . $db->quote($subscriber->email) . ', ' . $db->quote($mailing_list_id));

				$db->setQuery($query);
				$db->execute();
			}

			$db->transactionCommit();

			return $this->find($mailing_list_id);

		}
		catch (\Exception $e)
		{
			$db->transactionRollback();
			LogHelper::logError($e, __CLASS__);
			throw $e;
		}
	}

	public function findByEmail($email)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('id')))
				->from($db->quoteName('#__semantyca_nm_subscribers'))
				->where('email = ' . $db->quote($email));

			$db->setQuery($query);
			$db->execute();

			return $db->insertid();
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);
		}

		return 0;
	}

	public function upsertSubscriber($user_name, $email): int
	{
		try
		{
			$doc = $this->findByEmail($email);
			if ($doc == null)
			{
				$id = $this->addSubscriber($user_name, $email);
			}
			else
			{
				//TODO should be update instead
				$id = $doc->id;
			}

			return $id;
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);
		}

		return 0;
	}

	public function addSubscriber($user_name, $email): int
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query
				->insert($db->quoteName('#__semantyca_nm_subscribers'))
				->columns(array('name', 'email', 'mail_list_id'))
				->values($db->quote($user_name) . ', ' . $db->quote($email) . ', NULL');

			$db->setQuery($query);
			$db->execute();

			return $db->insertid();
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);
		}

		return 0;
	}

	public function remove($ids)
	{

		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);

			$escapedIds = array_map(function ($id) use ($db) {
				return $db->quote($id);
			}, $ids);

			$conditions = array(
				$db->quoteName('id') . ' IN (' . implode(',', $escapedIds) . ')'
			);

			$query->delete($db->quoteName('#__semantyca_nm_mailing_list'));
			$query->where($conditions);

			$db->setQuery($query);
			$result = $db->execute();

			if ($result)
			{
				return $db->getAffectedRows();
			}
			else
			{
				Log::add("The mail list deletion was failed", Log::WARNING, __CLASS__);

				return 0;
			}
		}
		catch
		(\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);
			throw $e;
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
