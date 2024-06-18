<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\RuntimeUtil;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;

class StatController extends BaseController
{
	public function display($cachable = false, $urlparams = array())
	{
		try
		{
			$view = $this->getView('Stat', 'html');
			$view->js_bundle = RuntimeUtil::getDynamicScriptUrl('js');
			$view->display();
		}
		catch (\Exception $e)
		{
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}

	public function findAll()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$currentPage  = $app->input->getInt('page', 1);
			$itemsPerPage = $app->input->getInt('limit', 10);

			/** @var StatModel $model */
			$model = $this->getModel('Stat');
			echo new JsonResponse($model->getList($currentPage, $itemsPerPage));
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			$app->close();
		}
	}

	public function getEvents()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$eventId = $app->input->getInt('eventid', 0);

			/** @var StatModel $model */
			$model = $this->getModel('Stat');
			echo new JsonResponse($model->getMergedEvents($eventId));
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			$app->close();
		}
	}



}
