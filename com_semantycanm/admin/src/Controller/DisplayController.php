<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use JFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $default_view = 'Dashboard';

	public function display($cachable = false, $urlparams = array())
	{

		$input = JFactory::getApplication()->input;
		$itemid = $input->get('Itemid', 0, 'int');
		$sitemenu = Factory::getApplication()->getMenu();
		$sitemenu->setActive($itemid);
		parent::display($cachable, $urlparams);

	}
}
