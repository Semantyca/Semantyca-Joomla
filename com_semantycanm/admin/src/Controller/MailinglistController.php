<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use JFactory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use JSession;
use JText;

class MailinglistController extends BaseController
{
	public function getList()
	{
		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'title')))
				->from($db->quoteName('#__usergroups'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());

			return null;
		}
	}

	public function addRecord()
	{
		try
		{
			error_log('addRecord method was triggered');
			if (!JSession::checkToken())
			{
				throw new \Exception(JText::_('JINVALID_TOKEN'));
			}
			$data = JFactory::getApplication()->input->post->getArray();
			$this->getModel()->addRecord($data['name']);
			echo json_encode(['success' => true]);
			JFactory::getApplication()->close();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
			Log::add('addRecord method was triggered', Log::INFO, 'com_semantycanm');
		}
	}

	public function removeRecords()
	{
		if (!JSession::checkToken())
		{
			throw new \Exception(JText::_('JINVALID_TOKEN'));
		}
		$data = JFactory::getApplication()->input->post->getArray();
		$this->getModel()->removeRecords(explode(',', $data['ids']));
		echo json_encode(['success' => true]);
		JFactory::getApplication()->close();
	}
}
