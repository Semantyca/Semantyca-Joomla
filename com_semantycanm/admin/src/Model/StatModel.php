<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

require_once '../Helper/Messaging.php';


use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use stdClass;

class StatModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'recipient', 'status', 'sent_time', 'reading_time')))->from($db->quoteName('#__nm_stats'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');

			return null;
		}
	}

	public function addStatRecord($recipient, $status, $newsletter_id, $subscriber_id): bool
	{
		try
		{
			$db       = $this->getDatabase();
			$query    = $db->getQuery(true);
			$reg_date = new Date();
			$columns  = array('recipient', 'reg_date', 'status', 'newsletter_id', 'subscriber_id');
			$values   = array($db->quote($recipient), $db->quote($reg_date->toSql()), $status, $newsletter_id, $subscriber_id);

			$query->insert($db->quoteName('#__nm_stats'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));

			$db->setQuery($query);
			$db->execute();

			return $db->insertid();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');

			return false;
		}

		return true;
	}

	public function updateStatRecord($id, $status): bool
	{
		try
		{
			$db             = $this->getDatabase();
			$record         = new stdClass();
			$record->id     = $id;
			$record->status = $status;
			if ($status == Constants::HAS_BEEN_SENT)
			{
				$record->sent_time = new Date();
			}
			elseif ($status == Constants::HAS_BEEN_READ)
			{
				$record->reading_time = new Date();
			}


			$result = $db->updateObject('#__nm_stats', $record, 'id');

			if (!$result)
			{
				throw new Exception('Failed to update record');
			}

			return true;
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');

			return false;
		}
	}


}
