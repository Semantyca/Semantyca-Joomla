<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class NewsLetterController extends BaseController
{
	public function findAll()
	{
		header('Content-Type: application/json; charset=UTF-8');
		try
		{
			$model   = $this->getModel('NewsLetter');
			$results = $model->getList();
			echo new JsonResponse($results);
		}
		catch (\Exception $e)
		{
			http_response_code(500);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function find()
	{
		header('Content-Type: application/json; charset=UTF-8');
		try
		{
			$id      = $this->input->getString('id');
			$model   = $this->getModel();
			$results = $model->find($id);
			echo new JsonResponse($results);
		}
		catch (\Exception $e)
		{
			http_response_code(500);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function add()
	{
		header('Content-Type: application/json; charset=UTF-8');
		$app   = Factory::getApplication();
		try
		{
			$input = $app->input;

			if ($input->getMethod() === 'POST')
			{
				$subj = $this->input->getString('subject');
				$msg  = $this->input->getString('msg');

				if (empty($msg))
				{
					throw new ValidationErrorException(['Message body is required']);
				}

				$model       = $this->getModel('NewsLetter');
				$results     = $model->upsert($subj, $msg);
				echo new JsonResponse($results);
			}
			else
			{
				throw new ValidationErrorException(['Only POST request is allowed']);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getErrors(), 'Error', true);
		}
		catch (\Exception $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getErrors(), 'error', true);
		}
		$app->close();
	}

	public function delete()
	{
		header('Content-Type: application/json; charset=UTF-8');
		$app   = Factory::getApplication();
		try
		{
			$app   = Factory::getApplication();
			$input = $app->input;

			if ($input->getMethod() === 'DELETE')
			{
				$ids_to_delete      = $this->input->getString('ids');
				$mailing_list_model = $this->getModel('NewsLetter');
				$results            = $mailing_list_model->remove($ids_to_delete);
				echo new JsonResponse($results);
			}
			else
			{
				throw new ValidationErrorException(['Only DELETE request is allowed']);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getErrors(), 'Error', true);
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
