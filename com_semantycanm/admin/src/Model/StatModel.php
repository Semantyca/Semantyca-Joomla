<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use DateTime;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Exception\UpdateRecordException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class StatModel extends BaseDatabaseModel
{
	public function getList($currentPage = 1, $itemsPerPage = 10)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$offset = ($currentPage - 1) * $itemsPerPage;

		$query
			->select([
				$db->quoteName('id', 'key'),
				$db->quoteName('reg_date'),
				$db->quoteName('newsletter_id'),
				$db->quoteName('status')
			])
			->from($db->quoteName('#__semantyca_nm_stats'))
			->order('reg_date desc')
			->setLimit($itemsPerPage, $offset);

		$db->setQuery($query);
		$stats = $db->loadObjectList();

		$queryCount = $db->getQuery(true)
			->select('COUNT(' . $db->quoteName('id') . ')')
			->from($db->quoteName('#__semantyca_nm_stats'));
		$db->setQuery($queryCount);
		$count   = $db->loadResult();
		$maxPage = (int) ceil($count / $itemsPerPage);

		return [
			'docs'    => $stats,
			'count'   => $count,
			'current' => $currentPage,
			'maxPage' => $maxPage
		];
	}

	public function getEvents($id)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select([
			$db->quoteName('id', 'key'),
			$db->quoteName('reg_date'),
			$db->quoteName('subscriber_email'),
			$db->quoteName('event_type'),
			$db->quoteName('fulfilled'),
			$db->quoteName('event_date'),
			$db->quoteName('errors')
		])
			->from($db->quoteName('#__semantyca_nm_subscriber_events'))
			->where($db->quoteName('stats_id') . ' = ' . (int) $id)
			->setLimit(100);

		$db->setQuery($query);

		return [
			'docs' => $db->loadObjectList()
		];
	}


	public function createStatRecord($status, $newsletter_id)
	{
		$db       = $this->getDatabase();
		$query    = $db->getQuery(true);
		$reg_date = new Date();

		$columns = array('reg_date', 'status', 'newsletter_id');

		$values = array(
			$db->quote($reg_date->toSql()),
			$status,
			$newsletter_id
		);

		$query->insert($db->quoteName('#__semantyca_nm_stats'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
		$db->setQuery($query);
		$db->execute();

		return $db->insertid();

	}

	/**
	 * @throws UpdateRecordException
	 * @since 1.0
	 */
	public function updateStatRecord($id, $status): bool
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('status') . ' = ' . $db->quote($status)
		);
		if ($status == Constants::DONE)
		{
			$fields[] = $db->quoteName('sent_time') . ' = COALESCE(' . $db->quoteName('sent_time') . ', ' . $db->quote((new DateTime())->format('Y-m-d H:i:s')) . ')';
		}

		$query->update($db->quoteName('#__semantyca_nm_stats'))
			->set($fields)
			->where($db->quoteName('id') . ' = ' . $db->quote($id));

		$db->setQuery($query);
		$result = $db->execute();
		if (!$result)
		{
			throw new UpdateRecordException('Failed to update record');
		}

		return true;

	}


}
