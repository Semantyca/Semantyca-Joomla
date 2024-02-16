<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Exception\UpdateRecordException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;

class NewsLetterModel extends BaseDatabaseModel
{
	public function getList($currentPage, $itemsPerPage)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$offset = ($currentPage - 1) * $itemsPerPage;

		$query
			->select([
				$db->quoteName('id', 'key'),
				$db->quoteName('reg_date'),
				$db->quoteName('subject'),
				$db->quoteName('message_content')
			])
			->from($db->quoteName('#__semantyca_nm_newsletters'))
			->order('reg_date desc')
			->setLimit($itemsPerPage, $offset);

		$db->setQuery($query);

		$documents = $db->loadObjectList();

		$queryCount = $db->getQuery(true)
			->select('COUNT(' . $db->quoteName('id') . ')')
			->from($db->quoteName('#__semantyca_nm_newsletters'));
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


	public function find($id)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'subject', 'message_content')))
			->from($db->quoteName('#__semantyca_nm_newsletters'))
			->where('id = ' . $db->quote($id));

		$db->setQuery($query);

		return $db->loadObjectList();

	}

	public function findRelevantEvent($id, $eventTypes)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$newslettersTable = $db->quoteName('#__semantyca_nm_newsletters');
		$statsTable       = $db->quoteName('#__semantyca_nm_stats');
		$eventsTable      = $db->quoteName('#__semantyca_nm_subscriber_events');

		if (!is_array($eventTypes))
		{
			$eventTypes = [$eventTypes];
		}

		$quotedEventTypes = array_map(function ($type) use ($db) {
			return $db->quote($type);
		}, $eventTypes);
		$eventTypesList   = implode(',', $quotedEventTypes);

		$query->select([
			$eventsTable . '.' . $db->quoteName('subscriber_email'),
			$eventsTable . '.' . $db->quoteName('event_type'),
			$eventsTable . '.' . $db->quoteName('fulfilled')
		])
			->from($newslettersTable)
			->innerJoin($statsTable . ' ON ' . $statsTable . '.newsletter_id = ' . $newslettersTable . '.id')
			->innerJoin($eventsTable . ' ON ' . $eventsTable . '.stats_id = ' . $statsTable . '.id')
			->where($newslettersTable . '.id = ' . $db->quote($id))
			->where($eventsTable . '.event_type IN (' . $eventTypesList . ')');

		$db->setQuery($query);

		return $db->loadObjectList();
	}


	public function findByContent($subject, $messageContent): ?object
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'subject', 'message_content')))
			->from($db->quoteName('#__semantyca_nm_newsletters'))
			->where('hash = ' . $db->quote(hash('sha256', $subject . $messageContent)));

		$db->setQuery($query);

		return $db->loadObject();

	}

	public function upsert($subjectValue, $messageContent): int
	{

		$newsLetter = $this->findByContent($subjectValue, $messageContent);
		if ($newsLetter == null)
		{
			$id = $this->add($subjectValue, $messageContent);
		}
		else
		{
			$id = $newsLetter->id;
		}

		return $id;


	}

	public function add($subject_value, $message_content): int
	{
		if ($subject_value == "")
		{
			$subject_value = Util::generateSubject();
		}
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->insert($db->quoteName('#__semantyca_nm_newsletters'))
			->columns(array('subject', 'message_content'))
			->values($db->quote($subject_value) . ', ' . $db->quote($message_content));
		$db->setQuery($query);
		$db->execute();

		return $db->insertid();

	}


	/**
	 * @throws UpdateRecordException
	 * @since 1.0
	 */
	public function remove($ids): int
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('id') . ' IN (' . $ids . ')'
		);

		$query->delete($db->quoteName('#__semantyca_nm_newsletters'));
		$query->where($conditions);

		$db->setQuery($query);
		$result = $db->execute();

		if ($result)
		{
			return $db->getAffectedRows();
		}
		else
		{
			throw new UpdateRecordException('The new letter deletion was failed');
		}
	}

}
