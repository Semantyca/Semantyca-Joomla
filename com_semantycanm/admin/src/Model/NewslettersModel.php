<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseDriver;
use Semantyca\Component\SemantycaNM\Administrator\DTO\NewsletterDTO;
use Semantyca\Component\SemantycaNM\Administrator\Exception\UpdateRecordException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class NewslettersModel extends BaseDatabaseModel
{
	public function getList($currentPage, $itemsPerPage)
	{
		$db     = $this->getDatabase();
		$query  = $db->getQuery(true);
		$offset = ($currentPage - 1) * $itemsPerPage;

		$query
			->select([
				$db->quoteName('n.id'),
				$db->quoteName('n.reg_date'),
				$db->quoteName('n.subject'),
				$db->quoteName('n.message_content'),
				$db->quoteName('s.status'),
				$db->quoteName('s.modified_date', 'status_modified_date')
			])
			->from($db->quoteName('#__semantyca_nm_newsletters', 'n'))
			->leftJoin($db->quoteName('#__semantyca_nm_sending_events', 's') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('s.newsletter_id'))
			->group($db->quoteName('n.id'))
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

	/**
	 * @throws Exception
	 * @since 1.0
	 */
	public function find($id)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName([
				'id',
				'reg_date',
				'subject',
				'use_wrapper',
				'template_id',
				'custom_fields_values',
				'articles_ids',
				'is_test',
				'mailing_list_ids',
				'test_email',
				'message_content',
			]))
			->from($db->quoteName('#__semantyca_nm_newsletters'))
			->where($db->quoteName('id') . ' = ' . $db->quote($id));

		$db->setQuery($query);

		$result = $db->loadObject();

		if ($result) {
			$dto = new NewsletterDTO();
			$dto->id = $result->id;
			$dto->regDate = new \DateTime($result->reg_date);
			$dto->subject = $result->subject;
			$dto->useWrapper = (bool)$result->use_wrapper;
			$dto->templateId = $result->template_id;
			$dto->customFieldsValues = $result->custom_fields_values;
			$dto->articlesIds = json_decode($result->articles_ids) ?? [];
			$dto->isTest = (bool)$result->is_test;
			$dto->mailingListIds = json_decode($result->mailing_list_ids) ?? [];
			$dto->testEmail = $result->test_email;
			$dto->messageContent = $result->message_content;

			return $dto;
		}

		return null;
	}


	public function findUnprocessedEvent($id, $event_type, $subscriber = null): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select($db->quoteName([
			'nl.subject',
			'nl.message_content',
			's.reg_date',
			'e.subscriber_email',
			'e.trigger_token'
		]))
			->from($db->quoteName('#__semantyca_nm_newsletters', 'nl'))
			->join('INNER', $db->quoteName('#__semantyca_nm_sending_events', 's') . ' ON ' . $db->quoteName('nl.id') . ' = ' . $db->quoteName('s.newsletter_id'))
			->join('INNER', $db->quoteName('#__semantyca_nm_subscriber_events', 'e') . ' ON ' . $db->quoteName('e.sending_id') . ' = ' . $db->quoteName('s.id'))
			->where($db->quoteName('e.event_type') . ' = ' . $event_type)
			->where($db->quoteName('e.fulfilled') . ' = -1')
			->where($db->quoteName('nl.id') . ' = ' . $db->quote($id));

		if ($subscriber)
		{
			$query->where($db->quoteName('e.subscriber_email') . ' = ' . $db->quote($subscriber));
		}

		$db->setQuery($query);
		$results = $db->loadObjectList();

		$map = [];
		foreach ($results as $result)
		{
			$subscriberEmail       = $result->subscriber_email;
			$map[$subscriberEmail] = (array) $result;
		}

		return $map;
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
			$eventsTable . '.' . $db->quoteName('fulfilled'),
			$newslettersTable . '.' . $db->quoteName('id')
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

	public function upsert(?int $id, NewsletterDTO $newsletter): int
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		if ($id === null) {
			$columns = [
				'reg_date', 'subject', 'use_wrapper', 'template_id', 'custom_fields_values',
				'articles_ids', 'is_test', 'mailing_list_ids', 'test_email', 'message_content'
			];

			$values = [
				$db->quote($newsletter->regDate->format('Y-m-d H:i:s')),
				$db->quote($newsletter->subject),
				$newsletter->useWrapper ? '1' : '0',
				$db->quote($newsletter->templateId),
				$db->quote(json_encode($newsletter->customFieldsValues)),
				$db->quote(json_encode($newsletter->articlesIds)),
				$newsletter->isTest ? '1' : '0',
				$db->quote(json_encode($newsletter->mailingListIds)),
				$db->quote($newsletter->testEmail),
				$db->quote($newsletter->messageContent)
			];

			$query
				->insert($db->quoteName('#__semantyca_nm_newsletters'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));

			$db->setQuery($query);
			$db->execute();

			return $db->insertid();
		} else {
			$fields = [
				$db->quoteName('modified_date') . ' = NOW()',
				$db->quoteName('subject') . ' = ' . $db->quote($newsletter->subject),
				$db->quoteName('use_wrapper') . ' = ' . ($newsletter->useWrapper ? '1' : '0'),
				$db->quoteName('template_id') . ' = ' . $db->quote($newsletter->templateId),
				$db->quoteName('custom_fields_values') . ' = ' . $db->quote(json_encode($newsletter->customFieldsValues)),
				$db->quoteName('articles_ids') . ' = ' . $db->quote(json_encode($newsletter->articlesIds)),
				$db->quoteName('is_test') . ' = ' . ($newsletter->isTest ? '1' : '0'),
				$db->quoteName('mailing_list_ids') . ' = ' . $db->quote(json_encode($newsletter->mailingListIds)),
				$db->quoteName('test_email') . ' = ' . $db->quote($newsletter->testEmail),
				$db->quoteName('message_content') . ' = ' . $db->quote($newsletter->messageContent)
			];

			$query
				->update($db->quoteName('#__semantyca_nm_newsletters'))
				->set($fields)
				->where($db->quoteName('id') . ' = ' . $db->quote($id));

			$db->setQuery($query);
			$db->execute();

			return $id;
		}
	}

	private function findByHash(string $hash): ?object
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(['id', 'hash']))
			->from($db->quoteName('#__semantyca_nm_newsletters'))
			->where($db->quoteName('hash') . ' = ' . $db->quote($hash));

		$db->setQuery($query);

		return $db->loadObject();
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
		$db     = $this->getDatabase();
		$query  = $db->getQuery(true);
		$hash   = hash('sha256', $subject_value . $message_content);
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
	public function delete($ids): int
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('id') . ' IN ('  . implode(',', array_map('intval', $ids)) . ')'
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
			throw new UpdateRecordException('The newsletters deletion failed');
		}
	}

	public function updateFulfilledForEvents(int $eventType, int $newsletterId, string $subscriber_email): void
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
			->where($db->quoteName('nl.id') . ' = ' . $db->quote($newsletterId))
			->where($db->quoteName('e.subscriber_email') . ' = ' . $db->quote($subscriber_email));

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
					->where($db->quoteName('id') . ' IN (' . implode(',', $eventIds) . ')');
				$db->setQuery($updateEventQuery);
				$db->execute();
			}
		}
	}

}
