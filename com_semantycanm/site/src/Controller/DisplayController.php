<?php

namespace Semantyca\Component\SemantycaNM\Site\Controller;

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $default_view = 'Dashboard';

	public function display($cachable = false, $urlparams = array())
	{
		try
		{

			$user = Factory::getApplication()->getIdentity();
			if ($user->id) {
				// User is logged in, get user data
				$userId = $user->id;
				$username = $user->username;
				$email = $user->email;
				$name = $user->name;
				error_log($name);
			}


			$mailing_list_model = $this->getModel('MailingList');
			$mailing_list_data  = $mailing_list_model->getList();
			$user_group_model = $this->getModel('UserGroup');
			$user_group_data  = $user_group_model->getList();




			$view  = $this->getView('Dashboard', 'html');
			$view->set('data', $mailing_list_data);
			$view->set('user_groups', $user_group_data);
			$view->set('mailing_lists', $mailing_list_data);
			$view->display();
		}
		catch (\Exception $e)
		{
			error_log($e);
			Log::add('addRecord method was triggered', Log::INFO, 'com_semantycanm');
		}
	}
}
