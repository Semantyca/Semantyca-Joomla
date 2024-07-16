<?php
/**
 * @package     SemantycaNM
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\ResponseHelper;
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
			echo ResponseHelper::error('error', $e->getMessage());
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
