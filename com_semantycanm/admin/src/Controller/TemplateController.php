<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Semantyca\Component\SemantycaNM\Administrator\Exception\DuplicatedEntityModelException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundModelException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;
use Semantyca\Component\SemantycaNM\Administrator\Helper\ResponseHelper;
use Semantyca\Component\SemantycaNM\Administrator\Model\NewslettersModel;
use Semantyca\Component\SemantycaNM\Administrator\Model\TemplateModel;
use Throwable;

class TemplateController extends BaseController
{
	public function findAll()
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$currentPage  = $this->input->getInt('page', 1);
			$itemsPerPage = $this->input->getInt('limit', 10);

			$model  = $this->getModel('Template');
			$result = $model->getList($currentPage, $itemsPerPage);

			echo ResponseHelper::success($result);
		}
		catch (Exception $e)
		{
			http_response_code(500);
			echo ResponseHelper::error('error', $e->getMessage());
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
			$id    = $this->input->getInt('id');
			$model = $this->getModel('Template');
			echo ResponseHelper::success($model->getTemplateById($id)->toArray());
		}
		catch (RecordNotFoundModelException $e)
		{
			http_response_code(404);
			echo ResponseHelper::error('applicationError', $e->getErrors());
		}
		catch (\Throwable $e)
		{
			http_response_code(500);
			echo ResponseHelper::error('error', $e->getMessage());
		}
		finally
		{
			Factory::getApplication()->close();
		}
	}

	public function upsert()
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id = $this->input->getString('id');

			$inputJSON = file_get_contents('php://input');
			$inputData = json_decode($inputJSON, true);

			$doc = $inputData ?? '';

			if (isset($doc['customFields']) && is_array($doc['customFields']))
			{
				foreach ($doc['customFields'] as $customField)
				{
					if ($customField['type'] == Constants::FIELD_TYPE_STRING_LIST || $customField['type'] == Constants::FIELD_TYPE_ARTICLE_LIST)
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

			/** @var TemplateModel $model */
			$model  = $this->getModel('Template');
			$result = $model->upsert($id, $doc);
			if ($result)
			{
				echo ResponseHelper::success([], 'Template saved successfully');
			}
			else
			{
				throw new Exception('Failed to update the template.');
			}
		}
		catch (DuplicatedEntityModelException $e)
		{
			http_response_code(400);
			echo ResponseHelper::error('duplicateError', $e->getMessage(), $e->getCode());
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(422);
			echo ResponseHelper::error('validationError', $e->getMessage());
		}
		catch (Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', $e->getMessage());
		}
		finally
		{
			$app->close();
		}
	}

	public function autoSave()
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$id = $this->input->getInt('id');
			if (empty($id))
			{
				throw new ValidationErrorException(['id is required for auto-save']);
			}

			$inputJSON = file_get_contents('php://input');
			$inputData = json_decode($inputJSON, true);

			if (!$inputData)
			{
				throw new ValidationErrorException(['Invalid JSON data provided.']);
			}

			$model  = $this->getModel('Template');
			$result = $model->autoSaveTemplate($id, $inputData);

			if ($result)
			{
				echo ResponseHelper::success([], 'Template auto-saved successfully.');
			}
			else
			{
				throw new Exception('Failed to auto-save the template.');
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo ResponseHelper::error('validationError', $e->getErrors());
		}
		catch (Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', $e->getMessage());
		}
		finally
		{
			$app->close();
		}
	}

	public function delete(): void
	{
		$app = Factory::getApplication();
		header(Constants::JSON_CONTENT_TYPE);
		try
		{
			$ids = $this->input->getString('ids');

			if (empty($ids) || $ids === 'null')
			{
				throw new ValidationErrorException(['id cannot be null']);
			}

			if (!is_array($ids))
			{
				$ids = explode(',', $ids);
			}

			$model  = $this->getModel('Template');
			$result = $model->delete($ids);

			if ($result)
			{
				$message = count($ids) > 1 ? 'Templates deleted successfully.' : 'Template deleted successfully.';
				echo ResponseHelper::success([], $message);
			}
			else
			{
				throw new Exception('Failed to delete the template(s).');
			}
		}
		catch (ValidationErrorException $e)
		{
			http_response_code(400);
			echo ResponseHelper::error('validationError', $e->getMessage());
		}
		catch (Throwable $e)
		{
			http_response_code(500);
			LogHelper::logException($e, __CLASS__);
			echo ResponseHelper::error('error', $e->getMessage());
		}
		finally
		{
			$app->close();
		}
	}
}