<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Messaging;


class ServiceController extends BaseController
{
	public function sendEmail()
	{
		$request = $this->input->post->get('body', '', 'RAW');
		$subject = $this->input->post->get('subject', '', 'RAW');
		$user_group = $this->input->post->get('user_group', '', 'RAW');
		Messaging::sendEmail($request, $subject, $user_group);
	}

}
