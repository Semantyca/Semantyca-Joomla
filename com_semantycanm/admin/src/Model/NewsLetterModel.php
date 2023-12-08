<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Util;

class NewsLetterModel extends BaseDatabaseModel
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('id', 'subject', 'reg_date', 'message_content')))
				->from($db->quoteName('#__nm_newsletters'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);

			return null;
		}
	}

	public function find($id)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('id', 'subject', 'message_content')))
				->from($db->quoteName('#__nm_newsletters'))
				->where('id = ' . $db->quote($id));

			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);

			return null;
		}
	}

	public function findByContent($subject, $content)
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('id', 'subject', 'message_content')))
				->from($db->quoteName('#__nm_newsletters'))
				->where('message_content = ' . $db->quote($content))
				->andWhere('subject = ' . $db->quote($subject));

			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);

			return null;
		}
	}

	public function upsert($subjectValue, $messageContent): int
	{
		try
		{
			$newsLetter = $this->findByContent($subjectValue, $messageContent);
			if ($newsLetter == null) {
				$id = $this->add($subjectValue, $messageContent);
			} else {
				//TODO should be update instead
				$id = $newsLetter->id;
			}
			return $id;
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}

		return 0;
	}

	public function add($subject_value, $message_content): int
	{
		try
		{
			if ($subject_value == "")
			{
				$subject_value = Util::generateSubject();
			}
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query
				->insert($db->quoteName('#__nm_newsletters'))
				->columns(array('subject', 'message_content'))
				->values($db->quote($subject_value) . ', ' . $db->quote($message_content));
			//error_log($query->dump());
			$db->setQuery($query);
			$db->execute();

			return $db->insertid();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}

		return 0;
	}


	public function remove($ids): int
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('id') . ' IN (' . $ids . ')'
		);

		$query->delete($db->quoteName('#__nm_newsletters'));
		$query->where($conditions);

		$db->setQuery($query);
		$result = $db->execute();

		if ($result)
		{
			return $db->getAffectedRows();
		}
		else
		{
			Log::add("The new letter deletion was failed", Log::WARNING, Constants::COMPONENT_NAME);

			return 0;
		}
	}

}
