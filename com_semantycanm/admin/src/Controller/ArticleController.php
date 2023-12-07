<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class ArticleController extends BaseController
{
	public function find()
	{
		try
		{
			$id = $this->input->getString('id');
			$model   = $this->getModel('Article');
			$results = $model->find($id);
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
	public function search()
	{
		try
		{
			$search = $this->input->getString('q');
			$article_model = $this->getModel('Article');
			$results = $article_model->search($search);
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
