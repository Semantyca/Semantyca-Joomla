<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Messaging;


class ServiceController extends BaseController
{
	public function sendEmail()
	{
		try
		{
			$request    = $this->input->post->get('body', '', 'RAW');
			$subject    = $this->input->post->get('subject', '', 'RAW');
			$user_group = $this->input->post->get('user_group', '', 'RAW');

			$mailingListModel = $this->getModel('MailingList');
			$statModel        = $this->getModel('Stat');

			(new Messaging($mailingListModel, $statModel))->sendEmail($request, $subject, $user_group);
		}
		catch
		(\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, 'com_semantycanm');
		}
	}


}
