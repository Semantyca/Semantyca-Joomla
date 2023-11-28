<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;


use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $default_view = 'Dashboard';

	public function display($cachable = false, $urlparams = array())
	{
		try
		{
			$mailing_list_model = $this->getModel('MailingList');
			$user_group_model   = $this->getModel('UserGroup');
			$article_model   = $this->getModel('Article');
			$news_letter_model   = $this->getModel('NewsLetter');

			$view = $this->getView('Dashboard', 'html');
			$view->set('user_groups', $user_group_model->getList());
			$view->set('mailing_lists',  $mailing_list_model->getList());
			$view->set('articles',  $article_model->getList());
			$view->set('news_letters', $news_letter_model->getList());
			$view->display();
		}
		catch (\Exception $e)
		{
			error_log($e);
			Log::add('addRecord method was triggered', Log::INFO, 'com_semantycanm');
		}
	}
}
