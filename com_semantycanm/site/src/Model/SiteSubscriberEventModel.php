<?php

namespace Semantyca\Component\SemantycaNM\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SiteSubscriberEventModel extends BaseDatabaseModel
{
	public function updateSubscriberEvent($triggerToken, $eventType): bool
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$currentDateTime = Factory::getDate()->toSql();

		$fields = [
			$db->quoteName('event_type') . ' = ' . $db->quote($eventType),
			$db->quoteName('expected') . ' = 0',
			$db->quoteName('event_date') . ' = ' . $db->quote($currentDateTime)
		];

		$conditions = [
			$db->quoteName('trigger_token') . ' = ' . $db->quote($triggerToken),
			$db->quoteName('expected') . ' = 1'
		];

		$query->update($db->quoteName('#__semantyca_nm_subscriber_events'))
			->set($fields)
			->where($conditions);

		$db->setQuery($query);

		return $db->execute();
	}

	public function getForStatRecord($newsletter_id)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('event_type'), 'COUNT(' . $db->quoteName('event_type') . ')'))
			->from($db->quoteName('#__semantyca_nm_subscriber_events'))
			->where($db->quoteName('newsletter_id') . ' = ' . $db->quote($newsletter_id))
			->where($db->quoteName('expected') . ' = 0')
			->group($db->quoteName('event_type'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

}
