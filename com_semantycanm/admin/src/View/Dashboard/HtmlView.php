<?php

namespace Semantyca\Component\SemantycaNM\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
	function display($tpl = null) {
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$user = Factory::getUser();

		if ($user->authorise('core.admin', 'com_semantycanm'))
		{
			\Joomla\CMS\Toolbar\ToolbarHelper::preferences('com_semantycanm');
		}
	}
}
