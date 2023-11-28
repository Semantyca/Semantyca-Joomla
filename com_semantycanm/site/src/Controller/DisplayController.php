<?php

namespace Semantyca\Component\SemantycaNM\Site\Controller;

defined('_JEXEC') or die;


use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $default_view = 'NewsLetters';

	public function display($cachable = false, $urlparams = array())
	{
		try
		{
			$mailing_list_model = $this->getModel('MailingList');
			$view = $this->getView('Dashboard', 'html');
			$view->set('mailing_lists',  $mailing_list_model->getList());
			$view->display();
		}
		catch (\Exception $e)
		{
			error_log($e);
			Log::add('addRecord method was triggered', Log::INFO, 'com_semantycanm');
		}
	}
}
