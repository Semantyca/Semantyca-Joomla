<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

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

			$model = $this->getModel('Stat');
			$results = $model->getList($currentPage, $itemsPerPage);
			$total   = $model->getTotalCount();


			$response = [
				'documents'  => $results,
				'total' => $total
			];

			echo new JsonResponse($response);
		}
		catch (\Exception $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			$app->close();
		}
	}

}
