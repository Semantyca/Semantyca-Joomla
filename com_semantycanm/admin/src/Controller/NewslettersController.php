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

use DateTime;
use Exception;
use InvalidArgumentException;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\DTO\NewsletterDTO;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewslettersModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\StatModel;
use Throwable;

class NewslettersController extends BaseController
{
	public function findAll()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$currentPage  = $app->input->getInt('page', 1);
			$itemsPerPage = $app->input->getInt('limit', 10);
			$model        = $this->getModel('Newsletters');
			echo new JsonResponse($model->getList($currentPage, $itemsPerPage));
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			$app->close();
		}
	}

	public function find()
	{
		try
		{
			$id      = $this->input->getString('id');
			$model   = $this->getModel('Newsletters');
			$results = $model->find($id);
			echo new JsonResponse($results);
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function findNewsletterEvents()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id = $this->input->getString('id');
			/** @var NewslettersModel $model */
			$model   = $this->getModel('Newsletters');
			$results = $model->findRelevantEvent($id, [Constants::EVENT_TYPE_DISPATCHED, Constants::EVENT_TYPE_READ]);
			echo new JsonResponse($results);
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function add()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$input = json_decode(file_get_contents('php://input'), true);

			if ($_SERVER['REQUEST_METHOD'] === 'POST')
			{
				$subj = $input['subject'] ?? null;
				$msg  = $input['msg'] ?? null;

				if (empty($msg))
				{
					throw new ValidationErrorException(['Message body is required']);
				}

				$model   = $this->getModel('Newsletters');
				$results = $model->upsert($subj, $msg);
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
			echo new JsonResponse($e->getMessage(), 'Error', true);
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		$app->close();
	}

	/**
	 * @throws Exception
	 * @since 1.0
	 */
	public function upsert()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$input = json_decode(file_get_contents('php://input'), true);

			if ($_SERVER['REQUEST_METHOD'] === 'POST')
			{
				// Validate input
				if (empty($input['messageContent']) || empty($input['wrapper']))
				{
					throw new ValidationErrorException(['Message content and wrapper are required']);
				}

				// Create NewsletterDTO from input
				$newsletterDTO = new NewsletterDTO();
				$newsletterDTO->regDate = new DateTime();
				$newsletterDTO->template_id = $input['template_id'] ?? 0;
				$newsletterDTO->customFieldsValues = json_encode($input['customFieldsValues'] ?? []);
				$newsletterDTO->articlesIds = $input['articlesIds'] ?? [];
				$newsletterDTO->isTest = $input['isTest'] ?? false;
				$newsletterDTO->mailingList = $input['mailingList'] ?? [];
				$newsletterDTO->testEmail = $input['testEmail'] ?? '';
				$newsletterDTO->messageContent = $input['messageContent'];
				$newsletterDTO->wrapper = $input['wrapper'];

				$model = $this->getModel('Newsletters');
				$result = $model->upsert($newsletterDTO);
				echo new JsonResponse(['id' => $result]);
			}
			else
			{
				throw new ValidationErrorException(['Only POST request is allowed']);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getMessage(), 'Error', true);
		}
		catch (Throwable $e)
		{
			http_response_code(500);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		$app->close();
	}

	public function delete()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();

		if ($_SERVER['REQUEST_METHOD'] !== 'DELETE')
		{
			http_response_code(405); // Method Not Allowed
			echo new JsonResponse('Method Not Allowed', 'error', true);
			$app->close();

			return;
		}

		try
		{
			$input = json_decode(file_get_contents('php://input'), true);
			$ids   = isset($input['ids']) ? $input['ids'] : [];

			if (empty($ids))
			{
				throw new InvalidArgumentException('No IDs provided');
			}

			/** @var StatModel $model */
			$model = $this->getModel('Stat');
			$model->deleteNewsletters($ids);

			echo new JsonResponse(['success' => true, 'message' => 'Newsletters deleted successfully']);
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			$app->close();
		}
	}
}
