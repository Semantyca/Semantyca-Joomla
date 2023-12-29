<?php

namespace Semantyca\Component\SemantycaNM\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Site\Helper\SiteConsts;

class SiteStatController extends BaseController
{
	public function postStat()
	{
		try
		{
			$id        = $this->input->getString('id');
			$statModel = $this->getModel('SiteStat');
			$statModel->updateOpens($id);
			header("Content-type: image/png");
			echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/wHtRbk7AAA=");
		}
		catch
		(\Exception $e)
		{
			header(SiteConsts::JSON_CONTENT_TYPE);
			http_response_code(500);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
