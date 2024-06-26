<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Absolute Management SIA. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class MailingListController extends BaseController
{
	/**
	 * @throws Exception
	 * @since 1.0
	 */
	public function findAll()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$currentPage  = $app->input->getInt('page', 1);
			$itemsPerPage = $app->input->getInt('limit', 10);
			$model = $this->getModel('MailingList');
			echo new JsonResponse($model->getList($currentPage, $itemsPerPage));
		}
		catch (Exception $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		finally
		{
			$app->close();
		}
	}

	public function find()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id       = $this->input->getString('id');
			$detailed = $this->input->getString('detailed');
			$model = $this->getModel('MailingList');
			$results  = $model->find($id, $detailed);
			echo new JsonResponse($results);
		}
		catch (Exception $e)
		{
			http_response_code(500);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		finally
		{
			Factory::getApplication()->close();
		}
	}

	/**
	 * @throws Exception
	 * @since 1.0
	 */
	public function upsert()
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id                 = $this->input->getString('id', null);
			$mailing_lst_name   = $this->input->getString('mailinglistname');
			$mailing_lists      = $this->input->getString('mailinglists');
			$mailing_list_model = $this->getModel('MailingList');

			if ($id) {
				$results = $mailing_list_model->update($id, $mailing_lst_name, $mailing_lists);
			} else {
				$results = $mailing_list_model->add($mailing_lst_name, $mailing_lists);
			}

			echo new JsonResponse($results);
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getMessage(), 'validationError', true);
		}
		catch (Exception $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		finally
		{
			$app->close();
		}
	}

	public function delete()
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$ids = $this->input->getString('ids');

			if (empty($ids) || $ids === 'null')
			{
				throw new ValidationErrorException([], 'id cannot be null');
			}

			if (!is_array($ids))
			{
				$ids = explode(',', $ids);
			}

			$mailing_list_model = $this->getModel('MailingList');
			$results = $mailing_list_model->remove($ids);
			echo new JsonResponse($results);
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getMessage(), 'validationError', true);
		}
		catch (Exception $e)
		{
			LogHelper::logException($e, __CLASS__);
			http_response_code(500);
			echo new JsonResponse($e->getMessage(), "error", true);
		}
		finally
		{
			$app->close();
		}
	}
}