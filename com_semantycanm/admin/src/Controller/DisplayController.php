<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class DisplayController extends BaseController
{
	protected $default_view = 'Dashboard';

	public function display($cachable = false, $urlparams = array())
	{
		try
		{
			//$mailing_list_model = $this->getModel('MailingList');
			$user_group_model   = $this->getModel('UserGroup');
			$article_model   = $this->getModel('Article');

			$view = $this->getView('Dashboard', 'html');
			$view->set('user_groups', $user_group_model->getList());
			//$view->set('mailing_lists',  $mailing_list_model->getList(1, 5));
			$view->set('articles',  $article_model->getList());
			$view->display();
		}
		catch (\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}
}
