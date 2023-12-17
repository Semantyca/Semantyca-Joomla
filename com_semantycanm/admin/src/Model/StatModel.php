<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use DateTime;
use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use stdClass;

class StatModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query
				->select($db
					->quoteName(array('id', 'recipient', 'newsletter_id', 'status', 'sent_time', 'reading_time')))
				->from($db
					->quoteName('#__semantyca_nm_stats'))
				->order('reg_date desc');
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);
			return null;
		}
	}

	public function addStat($recipient, $status, $newsletter_id, $subscriber_id): int
	{
		try
		{
			$db       = $this->getDatabase();
			$query    = $db->getQuery(true);
			$reg_date = new Date();
			$columns  = array('recipient', 'reg_date', 'status', 'newsletter_id', 'subscriber_id');
			$values   = array($db->quote($recipient), $db->quote($reg_date->toSql()), $status, $newsletter_id, $subscriber_id);

			$query->insert($db->quoteName('#__semantyca_nm_stats'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			error_log($query->__toString());
			$db->setQuery($query);
			$db->execute();

			return $db->insertid();
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);

			return 0;
		}
	}

	public function updateStatRecord($id, $status): bool
	{
		try
		{
			$db                   = $this->getDatabase();
			$record               = new stdClass();
			$record->id           = $id;
			$record->status       = $status;
			$record->sent_time    = null;
			$record->reading_time = null;
			if ($status == Constants::HAS_BEEN_SENT)
			{
				$record->sent_time = (new DateTime())->format('Y-m-d H:i:s');
			}
			elseif ($status == Constants::HAS_BEEN_READ)
			{
				$record->reading_time = (new DateTime())->format('Y-m-d H:i:s');
			}


			$result = $db->updateObject('#__semantyca_nm_stats', $record, 'id');

			if (!$result)
			{
				throw new Exception('Failed to update record');
			}

			return true;
		}
		catch (\Exception $e)
		{
			LogHelper::logError($e, __CLASS__);

			return false;
		}
	}


}
