<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\RuntimeUtil;

class MessageTemplatesController extends BaseController
{
	public function display($cachable = false, $urlparams = array())
	{
		try
		{
			$view = $this->getView('MessageTemplates', 'html');
			$view->js_bundles = RuntimeUtil::getDynamicScriptUrls('js');
			$view->display();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}
}
