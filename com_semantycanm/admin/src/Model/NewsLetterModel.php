<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;

class NewsLetterModel extends BaseDatabaseModel
{
	public function getList()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'subject', 'reg_date', 'message_content')))
			->from($db->quoteName('#__semantyca_nm_newsletters'))
			->order('reg_date desc');
		$db->setQuery($query);

		return $db->loadObjectList();
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
		//error_log($query->dump());
		$db->setQuery($query);
		$db->execute();

		return $db->insertid();

	}


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
			Log::add("The new letter deletion was failed", Log::WARNING, __CLASS__);

			return 0;
		}
	}

}
