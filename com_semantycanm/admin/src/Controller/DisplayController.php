<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\RuntimeUtil;

class DisplayController extends BaseController
{
	public function display($cachable = false, $urlparams = array())
	{
		try
		{
			$document = $this->app->getDocument();
			$viewType = $document->getType();
			$viewName = $this->input->get('view', $this->default_view);
			$viewLayout = $this->input->get('layout', 'default');

			$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
			$view->js_bundles = RuntimeUtil::getDynamicScriptUrls('js');

			if (!$view)
			{
				throw new \Exception("View not found: $viewName", 404);
			}

			$view->document = $document;
			$view->display();
		}
		catch (\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $this;
	}
}