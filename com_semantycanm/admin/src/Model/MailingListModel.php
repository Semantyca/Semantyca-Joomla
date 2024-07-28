<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Absolute Management SIA. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundModelException;

class MailingListModel extends BaseDatabaseModel
{
	public function getList($currentPage, $itemsPerPage)
	{
		$db     = $this->getDatabase();
		$query  = $db->getQuery(true);
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

	/**
	 * @throws RecordNotFoundModelException
	 * @since 1.0
	 */
	public function find($id, $detailed)
	{
		$db    = $this->getDatabase();
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
			throw new RecordNotFoundModelException(["Record not found for ID: $id"]);
		}

		if ($detailed)
		{
			$query = $db->getQuery(true);
			$query->select(
				array(
					$db->quoteName('u.id'),
					$db->quoteName('u.title')
				)
			)
				->from($db->quoteName('#__usergroups', 'u'))
				->join('INNER', $db->quoteName('#__semantyca_nm_mailing_list_rel_usergroups', 'm') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('m.user_group_id'))
				->where($db->quoteName('m.mailing_list_id') . ' = ' . $db->quote($mailingList->id));

			$db->setQuery($query);
			$mailingList->groups = $db->loadObjectList();
		}

		return $mailingList;
	}

	public function getEmailAddresses($mailing_list_name)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('DISTINCT ' . $db->quoteName('u.email'))
			->from($db->quoteName('#__semantyca_nm_mailing_list', 'ml'))
			->join('INNER', $db->quoteName('#__semantyca_nm_mailing_list_rel_usergroups', 'mlr') . ' ON ' . $db->quoteName('ml.id') . ' = ' . $db->quoteName('mlr.mailing_list_id'))
			->join('INNER', $db->quoteName('#__usergroups', 'ug') . ' ON ' . $db->quoteName('mlr.user_group_id') . ' = ' . $db->quoteName('ug.id'))
			->join('INNER', $db->quoteName('#__user_usergroup_map', 'ugm') . ' ON ' . $db->quoteName('ug.id') . ' = ' . $db->quoteName('ugm.group_id'))
			->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('ugm.user_id') . ' = ' . $db->quoteName('u.id'))
			->where($db->quoteName('ml.id') . ' IN (' . implode(',', array_map([$db, 'quote'], $mailing_list_name)) . ')');

		$db->setQuery($query);

		return $db->loadColumn();
	}

	public function upsert(int $id, string $mailingListName, $mailingListIds): object
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		try
		{
			$db->transactionStart();

			if ($id === -1)
			{
				$query
					->insert($db->quoteName('#__semantyca_nm_mailing_list'))
					->columns([$db->quoteName('name')])
					->values($db->quote($mailingListName));
				$db->setQuery($query);
				$db->execute();

				$id = $db->insertid();
			}
			else
			{
				$query
					->update($db->quoteName('#__semantyca_nm_mailing_list'))
					->set($db->quoteName('name') . ' = ' . $db->quote($mailingListName))
					->where($db->quoteName('id') . ' = ' . $db->quote($id));
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__semantyca_nm_mailing_list_rel_usergroups'))
					->where($db->quoteName('mailing_list_id') . ' = ' . $db->quote($id));
				$db->setQuery($query);
				$db->execute();
			}

			$this->insertUserGroups($id, $mailingListIds);

			$db->transactionCommit();

			return $this->find($id, false);
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			throw $e;
		}
	}

	public function delete($ids)
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

	private function insertUserGroups($mailing_list_id, $user_group_ids): void
	{
		$db = $this->getDatabase();
		//TODO FE can send empty element
		foreach (array_unique($user_group_ids) as $id)
		{
			$query = $db->getQuery(true);
			$query
				->insert($db->quoteName('#__semantyca_nm_mailing_list_rel_usergroups'))
				->columns(array('mailing_list_id', 'user_group_id'))
				->values($mailing_list_id . ', ' . $id);

			$db->setQuery($query);
			$db->execute();
		}
	}
}
