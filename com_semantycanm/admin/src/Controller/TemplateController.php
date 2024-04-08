<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Throwable;

class TemplateController extends BaseController
{
	public function find()
	{
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$name  = $this->input->getString('name');
			$model = $this->getModel('Template');
			echo new JsonResponse($model->getTemplateByName($name)->toArray());
		}
		catch (RecordNotFoundException $e)
		{
			http_response_code(404);
			echo new JsonResponse($e->getErrors(), 'applicationError', true);
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

	/**
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function update()
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id = $this->input->getString('id');

			$inputJSON = file_get_contents('php://input');
			$inputData = json_decode($inputJSON, true);

			$doc = $inputData['doc'] ?? '';

			if (empty($id))
			{
				throw new ValidationErrorException(['id is required']);
			}

			// Validate customFields defaultValue for specific field type to be a list
			if (isset($doc['customFields']) && is_array($doc['customFields']))
			{
				foreach ($doc['customFields'] as $customField)
				{
					if ($customField['type'] == Constants::FIELD_TYPE_STRING_LIST)
					{
						$defaultValue = $customField['defaultValue'] ?? '';
						// Attempt to decode the defaultValue to check if it's an array
						$decodedValue = json_decode($defaultValue);
						if (!is_array($decodedValue))
						{
							throw new ValidationErrorException(['defaultValue in customFields must be a list']);
						}
					}
				}
			}

			$model   = $this->getModel('Template');
			$results = $model->update($id, $doc);
			echo new JsonResponse($results);
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo new JsonResponse($e->getMessage(), 'validationError', true);
		}
		catch (Throwable $e)
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
