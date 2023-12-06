<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;

class NewsLetterController extends BaseController
{
	public function find()
	{
		try
		{
			$id = $this->input->getString('id');
			$model   = $this->getModel('NewsLetter');
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

	public function add()
	{
		try
		{
			$app   = Factory::getApplication();
			$input = $app->input;

			if ($input->getMethod() === 'POST')
			{
				$subj    = $this->input->getString('subject');
				$msg     = $this->input->getString('msg');
				$model   = $this->getModel('NewsLetter');
				$results = $model->add($subj, $msg);
				header('Content-Type: application/json; charset=UTF-8');
				echo new JsonResponse($results);
				Factory::getApplication()->close();
			}
			else
			{
				Log::add("Only POST request allowed", Log::WARNING, Constants::COMPONENT_NAME);
			}
		}
		catch (\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}

	public function delete()
	{
		try
		{
			$app   = Factory::getApplication();
			$input = $app->input;

			if ($input->getMethod() === 'DELETE')
			{
				$ids_to_delete      = $this->input->getString('ids');
				$mailing_list_model = $this->getModel('NewsLetter');
				$results            = $mailing_list_model->remove($ids_to_delete);
				header('Content-Type: application/json; charset=UTF-8');
				echo new JsonResponse($results);
				Factory::getApplication()->close();
			}
			else
			{
				Log::add("Only DELETE request allowed", Log::WARNING, Constants::COMPONENT_NAME);
			}
		}
		catch (\Exception $e)
		{
			error_log($e);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
		}
	}
}
