<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class ArticleController extends BaseController
{
	public function find()
	{
		header('Content-Type: application/json; charset=UTF-8');
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
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function search()
	{
		header('Content-Type: application/json; charset=UTF-8');
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
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
