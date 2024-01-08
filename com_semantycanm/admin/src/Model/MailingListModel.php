<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundException;

class MailingListModel extends BaseDatabaseModel
{
	public function getList($currentPage, $itemsPerPage)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$offset = ($currentPage - 1) * $itemsPerPage;

		$query
			->select($db->quoteName(array('id', 'name', 'reg_date')))
			->from($db->quoteName('#__semantyca_nm_mailing_list'))
			->order('reg_date DESC')
			->setLimit($itemsPerPage, $offset);

		$db->setQuery($query);
		$documents = $db->loadObjectList();

		$queryCount = $db->getQuery(true)
			->select('COUNT(' . $db->quoteName('id') . ')')
			->from($db->quoteName('#__semantyca_nm_mailing_list'));
		$db->setQuery($queryCount);
		$count   = $db->loadResult();
		$maxPage = (int) ceil($count / $itemsPerPage);

		return [
			'docs'    => $documents,
			'count'   => $count,
			'current' => $currentPage,
			'maxPage' => $maxPage
		];
	}


	public function find($id, $detailed)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select(
			array(
				$db->quoteName('#__semantyca_nm_mailing_list.name'),
				$db->quoteName('#__semantyca_nm_mailing_list.id'),
				$db->quoteName('#__semantyca_nm_mailing_list.reg_date')
			)
		)->from($db->quoteName('#__semantyca_nm_mailing_list'))
			->where($db->quoteName('#__semantyca_nm_mailing_list.id') . ' = ' . $db->quote($id));

		$db->setQuery($query);
		$mailingList = $db->loadObject();

		if (!$mailingList)
		{
			throw new RecordNotFoundException("Record not found for ID: $id");
		}

		if ($detailed)
		{
			$nestedQuery = $db->getQuery(true);
			$nestedQuery->select($db->quoteName(['ug.id', 'ug.title']))
				->from($db->quoteName('#__usergroups', 'ug'))
				->join('INNER', $db->quoteName('#__semantyca_nm_mailing_list_rel_usergroups', 'rel') . ' ON (' . $db->quoteName('ug.id') . ' = ' . $db->quoteName('rel.user_group_id') . ')')
				->join('INNER', $db->quoteName('#__semantyca_nm_mailing_list', 'ml') . ' ON (' . $db->quoteName('rel.mailing_list_id') . ' = ' . $db->quoteName('ml.id') . ')');

			$db->setQuery($nestedQuery);
			$mailingList->groups = $db->loadObjectList();
		}

		return $mailingList;
	}

	public function getSubscribers($mailing_list_name)
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

	/**
	 * @throws RecordNotFoundException
	 * @since 1.0
	 */
	public function add($mailing_list_name, $mailing_list_ids): object
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		try
		{
			$db->transactionStart();

			$query
				->insert($db->quoteName('#__semantyca_nm_mailing_list'))
				->columns(array('name'))
				->values(array($db->quote($mailing_list_name)));
			$db->setQuery($query);
			$db->execute();

			$mailing_list_id = $db->insertid();
			$this->insertUserGroups($mailing_list_id, explode(",", $mailing_list_ids));

			$db->transactionCommit();

			return $this->find($mailing_list_id, false);
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			throw $e;
		}

	}

	/**
	 * @throws Exception
	 * @since 1.0
	 */
	public function update($id, $mailing_lst_name, $mailing_list_ids): object
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		try
		{
			$db->transactionStart();

			$query
				->update($db->quoteName('#__semantyca_nm_mailing_list'))
				->set($db->quoteName('name') . ' = ' . $db->quote($mailing_lst_name))
				->where($db->quoteName('id') . ' = ' . $db->quote($id));
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__semantyca_nm_mailing_list_rel_usergroups'))
				->where($db->quoteName('mailing_list_id') . ' = ' . $db->quote($id));
			$db->setQuery($query);
			$db->execute();

			$this->insertUserGroups($id, explode(",", $mailing_list_ids));

			$db->transactionCommit();

			return $this->find($id, false);

		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			throw $e;
		}
	}


	public function findByEmail($email)
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

	public function upsertSubscriber($user_name, $email): int
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

	public function addSubscriber($user_name, $email): int
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

	public function remove($ids)
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
		$db->execute();

		return $db->getAffectedRows();

	}

	private function insertUserGroups($mailing_list_id, $user_group_ids)
	{
		$db = $this->getDatabase();

		foreach ($user_group_ids as $id)
		{
			$query = $db->getQuery(true);
			$query
				->insert($db->quoteName('#__semantyca_nm_mailing_list_rel_usergroups'))
				->columns(array('mailing_list_id', 'user_group_id'))
				->values($db->quote($mailing_list_id) . ', ' . $db->quote($id));

			$db->setQuery($query);
			$db->execute();
		}
	}
}
