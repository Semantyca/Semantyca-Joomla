<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SubscriberEventModel extends BaseDatabaseModel
{
	public function createSubscriberEvent($stat_id, $subscriber, $eventType): string
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$triggerToken = bin2hex(random_bytes(16));

		$data = [
			'subscriber_email' => $subscriber,
			'sending_id'       => $stat_id,
			'event_type'       => $eventType,
			'trigger_token'    => $triggerToken,
			'fulfilled'        => -1
		];

		$query->insert($db->quoteName('#__semantyca_nm_subscriber_events'))
			->columns($db->quoteName(array_keys($data)))
			->values(implode(',', $db->quote(array_values($data))));
		$db->setQuery($query);
		$db->execute();

		return $triggerToken;
	}

	public function deleteEventByTriggerToken($triggerToken): bool
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$conditions = [
			$db->quoteName('trigger_token') . ' = ' . $db->quote($triggerToken)
		];

		$query->delete($db->quoteName('#__semantyca_nm_subscriber_events'))
			->where($conditions);

		$db->setQuery($query);

		return $db->execute();
	}
}
