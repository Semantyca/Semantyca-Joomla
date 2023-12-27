<?php

namespace Semantyca\Component\SemantycaNM\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class ServiceController extends BaseController
{
	public function postStat()
	{
		try
		{
			$id        = $this->input->post->get('id', '', 'RAW');
			$statModel = $this->getModel('Stat');
			$statModel->updateStatRecord($id, Constants::HAS_BEEN_READ);
			header("Content-type: image/png");
			echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/wHtRbk7AAA=");
		}
		catch
		(\Exception $e)
		{
			header(Constants::JSON_CONTENT_TYPE);
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
