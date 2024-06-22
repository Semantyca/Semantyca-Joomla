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
			$view = $this->getView('Dashboard', 'html');
			$view->display();
		}
		catch (\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}


}
