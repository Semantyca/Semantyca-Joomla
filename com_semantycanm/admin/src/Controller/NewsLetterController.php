<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class NewsLetterController extends BaseController
{

	public function getList()
	{
		try
		{
			$mailing_list_model = $this->getModel('MailingList');
			$mailing_list_data  = $mailing_list_model->getList();
			$user_group_model   = $this->getModel('UserGroup');
			$user_group_data    = $user_group_model->getList();

			$view = $this->getView('Dashboard', 'html');
			$view->set('data', $mailing_list_data);
			$view->set('user_groups', $user_group_data);
			$view->set('mailing_lists', $mailing_list_data);
			$view->display();
		}
		catch (\Exception $e)
		{
			error_log($e->getMessage());
			return null;
		}
	}

}
