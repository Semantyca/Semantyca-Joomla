<?php
namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseDriver;
use Semantyca\Component\SemantycaNM\Administrator\Exception\UpdateRecordException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class NewsLetterModel extends BaseDatabaseModel
{
	public function getList($currentPage, $itemsPerPage)
	{
		$db     = $this->getDatabase();
		$query  = $db->getQuery(true);
		$offset = ($currentPage - 1) * $itemsPerPage;

		$query
			->select([
				$db->quoteName('n.id', 'key'),
				$db->quoteName('n.reg_date'),
				$db->quoteName('n.subject'),
				$db->quoteName('n.message_content'),
				'IFNULL(' . $db->quoteName('s.status') . ', 0) AS ' . $db->quoteName('status'),
				$db->quoteName('s.modified_date', 'status_modified_date')
			])
			->from($db->quoteName('#__semantyca_nm_newsletters', 'n'))
			->leftJoin($db->quoteName('#__semantyca_nm_sending_events', 's') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('s.newsletter_id'))
			->order($db->quoteName('n.reg_date') . ' DESC')
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

	public function findUnprocessedEvent($id)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select($db->quoteName([
			'nl.subject',
			'nl.message_content',
			's.id',
			's.reg_date',
			'e.subscriber_email',
			'e.trigger_token',
			'e.id']))
			->from($db->quoteName('#__semantyca_nm_newsletters', 'nl'))
			->join('INNER', $db->quoteName('#__semantyca_nm_sending_events', 's') . ' ON ' . $db->quoteName('nl.id') . ' = ' . $db->quoteName('s.newsletter_id'))
			->join('INNER', $db->quoteName('#__semantyca_nm_subscriber_events', 'e') . ' ON ' . $db->quoteName('e.sending_id') . ' = ' . $db->quoteName('s.id'))
			->where($db->quoteName('e.event_type') . ' = 100')
			->where($db->quoteName('e.fulfilled') . ' = -1')
			->where($db->quoteName('nl.id') . ' = ' . $db->quote($id));

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function findRelevantEvent($id, $eventTypes)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$newslettersTable = $db->quoteName('#__semantyca_nm_newsletters');
		$statsTable       = $db->quoteName('#__semantyca_nm_sending_events');
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
			->innerJoin($eventsTable . ' ON ' . $eventsTable . '.sending_id = ' . $statsTable . '.id')
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
			//$this->update($id, $subjectValue, $messageContent);
		}

		return $id;
	}

	public function add($subject_value, $message_content): int
	{
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

	public function update($id, $subject_value, $message_content): void
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$hash  = hash('sha256', $subject_value . $message_content);
		$fields = [
			$db->quoteName('subject') . ' = ' . $db->quote($subject_value),
			$db->quoteName('message_content') . ' = ' . $db->quote($message_content),
			$db->quoteName('hash') . ' = ' . $db->quote($hash)
		];

		$query
			->update($db->quoteName('#__semantyca_nm_newsletters'))
			->set($fields)
			->where($db->quoteName('id') . ' = ' . $db->quote($id));
		$db->setQuery($query);
		$db->execute();
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
			throw new UpdateRecordException('The newsletter deletion failed');
		}
	}

	public function updateFulfilledForEvents(int $eventType, int $newsletterId): void
	{
		/** @var DatabaseDriver $db */
		$db = $this->getDatabase();

		$query = $db->getQuery(true);
		$query->select('e.id AS event_id')
			->from($db->quoteName('#__semantyca_nm_newsletters', 'nl'))
			->innerJoin($db->quoteName('#__semantyca_nm_sending_events', 's') . ' ON ' . $db->quoteName('nl.id') . ' = ' . $db->quoteName('s.newsletter_id'))
			->leftJoin($db->quoteName('#__semantyca_nm_subscriber_events', 'e') . ' ON ' . $db->quoteName('e.sending_id') . ' = ' . $db->quoteName('s.id'))
			->where($db->quoteName('e.event_type') . ' = ' . $db->quote($eventType))
			->where($db->quoteName('e.fulfilled') . ' = ' . Constants::NOT_FULFILLED)
			->where($db->quoteName('nl.id') . ' = ' . $db->quote($newsletterId));

		$db->setQuery($query);
		$records = $db->loadObjectList();

		if (!empty($records))
		{
			$eventIds = array_column($records, 'event_id');

			if (!empty($eventIds))
			{
				$updateEventQuery = $db->getQuery(true)
					->update($db->quoteName('#__semantyca_nm_subscriber_events'))
					->set($db->quoteName('fulfilled') . ' = ' . Constants::DONE)
					->where($db->quoteName('id') . ' IN (' . implode(',', $db->quote($eventIds)) . ')');
				$db->setQuery($updateEventQuery);
				$db->execute();
			}
		}
	}

}
