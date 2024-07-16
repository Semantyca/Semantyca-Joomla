<?php

namespace Semantyca\Component\SemantycaNM\Site\Controller;

defined('_JEXEC') or die;
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Site\Helper\SiteConsts;

class SiteSubscriberEventController extends BaseController
{
	public function postEvent()
	{
		try
		{
			$token     = $this->input->getString('id');
			$statModel = $this->getModel('SiteSubscriberEvent');
			$statModel->updateSubscriberEvent($token, SiteConsts::EVENT_TYPE_READ);
			header(SiteConsts::IMAGE_CONTENT_TYPE);
			echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/wHtRbk7AAA=");
		}
		catch
		(\Exception $e)
		{
			header(SiteConsts::JSON_CONTENT_TYPE);
			http_response_code(500);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
