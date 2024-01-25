<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
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
			$user_group_model = $this->getModel('UserGroup');

			$view = $this->getView('Dashboard', 'html');
			$view->set('user_groups', $user_group_model->getList());
			$view->set('js_bundle', $this->getDynamicScriptUrl('js'));
			//$view->set('css_bundle', $this->getDynamicScriptUrl('css'));
			$view->set('tinymce_lic', $this->getTinyMCELic());
			$view->display();
		}
		catch (\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}

	private function getDynamicScriptUrl($type): ?string
	{
		$relativeDirectory = "/components/com_semantycanm/assets/bundle";
		$directory         = JPATH_ADMINISTRATOR . $relativeDirectory;
		$prefix            = "bundle-";

		if (!file_exists($directory) || !is_dir($directory))
		{
			return null;
		}

		$files = scandir($directory);
		foreach ($files as $file)
		{
			if (strpos($file, $prefix) === 0 && pathinfo($file, PATHINFO_EXTENSION) === $type)
			{
				return "bundle/" . $file;
			}
		}

		return null;
	}

	private function getTinyMCELic()
	{
		$params = ComponentHelper::getParams(Constants::COMPONENT_NAME);

		return $params->get('tinymce_license_key', "");
	}
}
