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


}
