<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Controller;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\ValidationErrorException;
use Semantyca\Component\SemantycaNM\Administrator\Helper\LogHelper;

class TemplateController extends BaseController
{
	public function find()
	{
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
		catch (\Exception $e)
		{
			http_response_code(500);
			echo new JsonResponse($e->getMessage(), 'error', true);
		} finally
		{
			Factory::getApplication()->close();
		}
	}

	public function update()
	{
		$app = Factory::getApplication();
		try
		{
			$id = $this->input->getString('id');

			$inputJSON = file_get_contents('php://input');
			$inputData = json_decode($inputJSON, true);

			$html = $inputData['html'] ?? '';

			if (empty($id))
			{
				throw new ValidationErrorException(['id is required']);
			}

			$model   = $this->getModel('Template');
			$results = $model->update($id, $html);
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
		} finally
		{
			$app->close();
		}
	}


}