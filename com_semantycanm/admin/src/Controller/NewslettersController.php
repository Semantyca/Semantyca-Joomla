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
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\NewsletterValidator;
use Semantyca\Component\SemantycaNM\Administrator\Helper\ResponseHelper;

class NewslettersController extends BaseController
{
	public function findAll()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$currentPage  = $app->input->getInt('page', 1);
			$itemsPerPage = $app->input->getInt('size', 10);
			$model        = $this->getModel('Newsletters');
			echo ResponseHelper::success($model->getList($currentPage, $itemsPerPage));
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', 500, $e->getMessage());
		}
		$app->close();
	}

	public function find()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$id = $this->input->getInt('id');
			if (!$id)
			{
				throw new ValidationErrorException(['Invalid or missing ID']);
			}
			$model      = $this->getModel('Newsletters');
			$newsletter = $model->find($id);

			if (!$newsletter)
			{
				http_response_code(404);
				echo ResponseHelper::error('notFound', 'Newsletter not found');
			}
			else
			{
				echo ResponseHelper::success($newsletter);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(422);
			echo ResponseHelper::error('validationError', 422, $e->getErrors());
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', 500, $e->getMessage());
		}
		$app->close();
	}

	public function upsert(): void
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$input = json_decode(file_get_contents('php://input'), true);

			if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			{
				throw new ValidationErrorException(['Only POST request is allowed']);
			}

			$newsletterDTO = NewsletterValidator::validateAndCreateDTO($input);

			$model = $this->getModel('Newsletters');
			$id = $app->input->getInt('id', 0);
			$result = $model->upsert($id, $newsletterDTO);
			echo ResponseHelper::success(['id' => $result], 'Newsletter saved successfully');
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(422);
			echo ResponseHelper::error('validationError', 422, $e->getErrors());
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', 500, $e->getMessage());
		}
		$app->close();
	}

	public function delete(): void
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();

		if ($_SERVER['REQUEST_METHOD'] !== 'DELETE')
		{
			http_response_code(405);
			echo ResponseHelper::error('methodNotAllowed', 'Method Not Allowed');
			$app->close();
			return;
		}

		try
		{
			$input = json_decode(file_get_contents('php://input'), true);
			$ids   = $input['ids'] ?? [];

			if (empty($ids))
			{
				throw new ValidationErrorException(['No IDs provided']);
			}

			$model = $this->getModel('Newsletters');
			$model->delete($ids);

			echo ResponseHelper::success([], 'Newsletters deleted successfully');
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo ResponseHelper::error('validationError', 400, $e->getErrors());
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', 500, $e->getMessage());
		}
		$app->close();
	}
}