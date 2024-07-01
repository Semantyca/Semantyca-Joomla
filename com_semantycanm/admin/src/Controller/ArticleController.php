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

class ArticleController extends BaseController
{
	public function find()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id = $this->input->getString('id');
			$model   = $this->getModel('Article');
			$results = $model->find($id);
			echo new JsonResponse($results);
		}
		catch (\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function search()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$search  = $this->input->getString('q');
			$article_model = $this->getModel('Article');
			$results = $article_model->search($search);
			echo new JsonResponse($results);
		}
		catch (\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
