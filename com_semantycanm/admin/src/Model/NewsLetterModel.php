<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use JLog;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class NewsLetterModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'subject', 'send_date', 'message_content')))->from($db->quoteName('#__nm_newsletters'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'com_semantycanm');

			return null;
		}
	}

}
