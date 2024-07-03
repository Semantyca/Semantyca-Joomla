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
		catch (Throwable $e)
		{
			http_response_code(500);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		$app->close();
	}

	public function find()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$id    = $this->input->getString('id');
			$model = $this->getModel('Newsletters');
			echo new JsonResponse($model->find($id));
		}
		catch (Throwable $e)
		{
			http_response_code(500);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		$app->close();
	}

	public function findNewsletterEvents()
	{
		header(Constants::JSON_CONTENT_TYPE);
		$app = Factory::getApplication();
		try
		{
			$id    = $this->input->getString('id');
			$model = $this->getModel('Newsletters');
			echo new JsonResponse($model->findRelevantEvent($id, [Constants::EVENT_TYPE_DISPATCHED, Constants::EVENT_TYPE_READ]));
		}
		catch (Throwable $e)
		{
			http_response_code(500);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		$app->close();
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

				$model = $this->getModel('Newsletters');
				echo new JsonResponse($model->upsert($subj, $msg));
			}
			else
			{
				throw new ValidationErrorException(['Only POST request is allowed']);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			$errors  = $e->getErrors();
			$message = $e->getMessage();
			echo new JsonResponse(['errors' => $errors, 'message' => $message], 'Error', true);
		}
		catch (Throwable $e)
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
				if (empty($input['messageContent']))
				{
					throw new ValidationErrorException(['Message content is required']);
				}

				if (empty($input['subject']))
				{
					throw new ValidationErrorException(['Subject is required']);
				}

				$isTest = $input['isTest'] ?? false;

				if (empty($input['template_id']))
				{
					throw new ValidationErrorException(['Template ID is required']);
				}

				if ($isTest)
				{
					if (empty($input['testEmail']))
					{
						throw new ValidationErrorException(['Test email is required when isTest is true']);
					}
				}
				else
				{
					if (empty($input['mailingList']))
					{
						throw new ValidationErrorException(['Mailing list is required when isTest is false']);
					}
				}

				$newsletterDTO                     = new NewsletterDTO();
				$newsletterDTO->regDate            = new DateTime();
				$newsletterDTO->template_id        = $input['template_id'];
				$newsletterDTO->customFieldsValues = json_encode($input['customFieldsValues'] ?? []);
				$newsletterDTO->articlesIds        = $input['articlesIds'] ?? [];
				$newsletterDTO->isTest             = $isTest;
				$newsletterDTO->mailingList        = $input['mailingList'] ?? [];
				$newsletterDTO->testEmail          = $input['testEmail'] ?? '';
				$newsletterDTO->subject            = $input['subject'];
				$newsletterDTO->messageContent     = $input['messageContent'];
				$newsletterDTO->useWrapper         = $input['useWrapper'];

				$model = $this->getModel('Newsletters');
				echo new JsonResponse(['id' => $model->upsert($newsletterDTO)]);
			}
			else
			{
				throw new ValidationErrorException(['Only POST request is allowed']);
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			$errors  = $e->getErrors();
			$message = $e->getMessage();
			echo new JsonResponse(['errors' => $errors, 'message' => $message], 'Error', true);
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
			http_response_code(405);
			echo new JsonResponse('Method Not Allowed', 'error', true);
			$app->close();

			return;
		}

		try
		{
			$input = json_decode(file_get_contents('php://input'), true);
			$ids   = $input['ids'] ?? [];

			if (empty($ids))
			{
				throw new InvalidArgumentException('No IDs provided');
			}

			$model = $this->getModel('Stat');
			$model->deleteNewsletters($ids);

			echo new JsonResponse(['success' => true, 'message' => 'Newsletters deleted successfully']);
		}
		catch (Throwable $e)
		{
			http_response_code(500);
			Log::add($e->getMessage(), Log::ERROR, Constants::COMPONENT_NAME);
			echo new JsonResponse($e->getMessage(), 'error', true);
		}
		$app->close();
	}
}