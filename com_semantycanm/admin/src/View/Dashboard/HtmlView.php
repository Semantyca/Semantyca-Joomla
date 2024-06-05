<?php

namespace Semantyca\Component\SemantycaNM\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use JText;

class HtmlView extends BaseHtmlView
{
	function display($tpl = null) {
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_semantycanm', JPATH_ADMINISTRATOR) || $lang->load('com_semantycanm', JPATH_SITE);

		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.admin', 'com_semantycanm'))
		{
			ToolbarHelper::preferences('com_semantycanm');
		}

		ToolbarHelper::title(JText::_('COM_SEMANTYCANM_CONFIGURATION'), 'icon-semantic');
	}
}
