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

			if (isset($doc['customFields']) && is_array($doc['customFields']))
			{
				foreach ($doc['customFields'] as $customField)
				{
					if ($customField['type'] == Constants::FIELD_TYPE_STRING_LIST)
					{
						$defaultValue = $customField['defaultValue'] ?? '';
						$decodedValue = json_decode($defaultValue);
						if (!is_array($decodedValue))
						{
							throw new ValidationErrorException(['defaultValue in customFields must be a list']);
						}
					}
				}
			}

			$model   = $this->getModel('Template');
			$result = $model->update($id, $doc);
			if ($result)
			{
				echo new JsonResponse(['success' => true, 'message' => 'Template saved successfully.']);
			}
			else
			{
				throw new Exception('Failed to update the template.');
			}

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

	/**
	 * Deletes a template.
	 *
	 * @since 1.0.0
	 */
	public function delete(): void
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id = $this->input->getInt('id');

			if (empty($id))
			{
				throw new ValidationErrorException(['id is required for deletion']);
			}

			$model  = $this->getModel('Template');
			$result = $model->deleteTemplate($id);

			if ($result)
			{
				echo new JsonResponse(['success' => true, 'message' => 'Template deleted successfully.']);
			}
			else
			{
				throw new Exception('Failed to delete the template.');
			}
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
