<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class StatController extends BaseController
{
	public function findAll()
	{
		try
		{
			$model   = $this->getModel();
			$results = $model->getList();
			header('Content-Type: application/json; charset=UTF-8');
			echo new JsonResponse($results);
			Factory::getApplication()->close();

		}
		catch (\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}
}
