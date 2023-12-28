<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class MailingListController extends BaseController
{
	public function findAll()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$model   = $this->getModel();
			$results = $model->getList();
			Factory::getApplication();
			echo new JsonResponse($results);

		}
		catch (\Exception $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getErrors(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function add()
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$input = $app->input;
			if ($input->getMethod() === 'POST')
			{
				$mailing_lst_name   = $this->input->getString('mailinglistname');
				$mailing_lists      = $this->input->getString('mailinglists');
				$mailing_list_model = $this->getModel('MailingList');
				$user_group_model   = $this->getModel('UserGroup');
				$results            = $mailing_list_model->add($user_group_model, $mailing_lst_name, $mailing_lists);
				echo new JsonResponse($results);
			}
			else
			{
				throw new ValidationErrorException(["Only POST request allowed"]);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getErrors(), 'validationError', true);
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

	public function delete()
	{
		try
		{
			$app = Factory::getApplication();
			header(Constants::JSON_CONTENT_TYPE);
			$input = $app->input;
			$ids   = $this->input->getString('ids');
			//TODO ids should be be a list
			if (empty($ids) || $ids === 'null')
			{
				throw new ValidationErrorException(['id cannot be null']);
			}

			if (!is_array($ids))
			{
				$ids = explode(',', $ids);
			}

			if ($input->getMethod() === 'DELETE')
			{
				$mailing_list_model = $this->getModel('MailingList');
				$results            = $mailing_list_model->remove($ids);
				echo new JsonResponse(['success' => true, 'payload' => $results]);
			}
			else
			{
				throw new ValidationErrorException(["Only DELETE request allowed"]);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getErrors(), 'validationError', true);
		}
		catch (\Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			http_response_code(500);
			echo new JsonResponse($e, "exception", true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}
}
