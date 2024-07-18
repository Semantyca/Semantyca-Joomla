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
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\ResponseHelper;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;

class StatController extends BaseController
{

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
			echo ResponseHelper::error('error', 500, $e->getMessage());
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
			echo ResponseHelper::success($model->getMergedEvents($eventId));
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', 500, $e->getMessage());
		} finally
		{
			$app->close();
		}
	}


}
