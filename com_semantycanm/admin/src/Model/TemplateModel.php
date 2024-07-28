<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\Model;

use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Semantyca\Component\SemantycaNM\Administrator\DTO\TemplateDTO;
use Semantyca\Component\SemantycaNM\Administrator\Exception\DuplicatedEntityModelException;
use Semantyca\Component\SemantycaNM\Administrator\Exception\RecordNotFoundModelException;


class TemplateModel extends BaseDatabaseModel
{

	/**
	 * @throws Exception
	 * @since 1.0
	 */
	public function getList($currentPage, $itemsPerPage, $currentTemplateId = null): array
	{
		$db                  = $this->getDatabase();
		$query               = $db->getQuery(true);
		$offset              = ($currentPage - 1) * $itemsPerPage;
		$currentTemplateHash = '';

		if ($currentTemplateId !== null)
		{
			$currentTemplateQuery = $db->getQuery(true)
				->select('hash')
				->from($db->quoteName('#__semantyca_nm_templates'))
				->where($db->quoteName('id') . ' = ' . (int) $currentTemplateId);
			$db->setQuery($currentTemplateQuery);
			$currentTemplateHash = $db->loadResult();
		}

		$query
			->select('id, reg_date, name, type, description, content, wrapper, hash')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->order(array('reg_date DESC'))
			->setLimit($itemsPerPage, $offset);
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$templates = [];
		foreach ($rows as $row)
		{
			$template               = new TemplateDTO();
			$template->id           = $row->id;
			$template->regDate      = new \DateTime($row->reg_date);
			$template->name         = $row->name;
			$template->type         = $row->type;
			$template->description  = $row->description;
			$template->content      = $row->content;
			$template->wrapper      = $row->wrapper;
			$template->hash         = $row->hash;
			$template->isCompatible = ($row->hash === $currentTemplateHash);

			$customFieldsQuery = $db->getQuery(true)
				->select('id, template_id, name, type, caption, default_value, is_available')
				->from($db->quoteName('#__semantyca_nm_custom_fields'))
				->where('template_id = ' . (int) $row->id);
			$db->setQuery($customFieldsQuery);
			$customFieldsRows = $db->loadObjectList();
			foreach ($customFieldsRows as $field)
			{
				$template->customFields[] = [
					'id'           => $field->id,
					'name'         => $field->name,
					'type'         => $field->type,
					'caption'      => $field->caption,
					'defaultValue' => $field->default_value,
					'isAvailable'  => $field->is_available,
				];
			}

			$templates[] = $template;
		}
		$queryCount = $db->getQuery(true)
			->select('COUNT(' . $db->quoteName('id') . ')')
			->from($db->quoteName('#__semantyca_nm_templates'));
		$db->setQuery($queryCount);
		$totalItems = $db->loadResult();
		$maxPage    = (int) ceil($totalItems / $itemsPerPage);

		return [
			'docs'    => $templates,
			'count'   => $totalItems,
			'current' => $currentPage,
			'maxPage' => $maxPage
		];
	}

	/**
	 * @throws RecordNotFoundModelException
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function getTemplateById($id): TemplateDTO
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('id, reg_date, name, type, description, content, wrapper')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('id = ' . (int) $id);

		$db->setQuery($query);
		$row = $db->loadObject();

		if (empty($row))
		{
			throw new RecordNotFoundModelException(["The template has not been found"]);
		}

		$template              = new TemplateDTO();
		$template->id          = $row->id;
		$template->regDate     = new \DateTime($row->reg_date);
		$template->content     = $row->content;
		$template->name        = $row->name;
		$template->type        = $row->type;
		$template->description = $row->description;
		$template->wrapper     = $row->wrapper;
		$template->hash        = $row->hash;

		$customFieldsQuery = $db->getQuery(true)
			->select('id, template_id, name, type, caption, default_value, is_available')
			->from($db->quoteName('#__semantyca_nm_custom_fields'))
			->where('template_id = ' . (int) $row->id);
		$db->setQuery($customFieldsQuery);
		$customFieldsRows = $db->loadObjectList();

		foreach ($customFieldsRows as $customFieldRow)
		{
			$decodedDefaultValue      = $customFieldRow->default_value;
			$template->customFields[] = [
				'id'           => $customFieldRow->id,
				'name'         => $customFieldRow->name,
				'type'         => $customFieldRow->type,
				'caption'      => $customFieldRow->caption,
				'defaultValue' => $decodedDefaultValue,
				'isAvailable'  => $customFieldRow->is_available,
			];
		}

		return $template;
	}

	/**
	 * @throws RecordNotFoundModelException
	 * @throws Exception
	 * @since 1.0.0
	 * @deprecated
	 */

	public function getTemplateByName($name): TemplateDTO
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('id, reg_date, name, type, description, content, wrapper')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('name = ' . $db->quote($name));

		$db->setQuery($query);
		$row = $db->loadObject();

		if (empty($row))
		{
			throw new RecordNotFoundModelException(["The template has not been found"]);
		}

		$template              = new TemplateDTO();
		$template->id          = $row->id;
		$template->regDate     = new \DateTime($row->reg_date);
		$template->content     = $row->content;
		$template->name        = $row->name;
		$template->type        = $row->type;
		$template->description = $row->description;
		$template->wrapper     = $row->wrapper;

		$customFieldsQuery = $db->getQuery(true)
			->select('id, template_id, name, type, caption, default_value, is_available')
			->from($db->quoteName('#__semantyca_nm_custom_fields'))
			->where('template_id = ' . (int) $row->id);
		$db->setQuery($customFieldsQuery);
		$customFieldsRows = $db->loadObjectList();

		foreach ($customFieldsRows as $customFieldRow)
		{
			$decodedDefaultValue      = $customFieldRow->default_value;
			$template->customFields[] = [
				'id'           => $customFieldRow->id,
				'name'         => $customFieldRow->name,
				'type'         => $customFieldRow->type,
				'caption'      => $customFieldRow->caption,
				'defaultValue' => $decodedDefaultValue,
				'isAvailable'  => $customFieldRow->is_available,
			];
		}


		return $template;
	}


	public function find($type, $name)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('type = ' . (int) $type)
			->where('name = ' . (int) $name);
		$db->setQuery($query);

		return $db->loadObject();
	}


	public function upsert($id, $messageContent): bool
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$customFieldsData = [];
		foreach ($messageContent['customFields'] as $customField)
		{
			$customFieldsData[] = $customField['name'] . '|' . $customField['type'];
		}
		sort($customFieldsData);
		$templateHash = md5(implode(',', $customFieldsData));

		if ($id === "")
		{
			$exists = false;
		}
		else
		{
			$query->select($db->quoteName('id'))
				->from($db->quoteName('#__semantyca_nm_templates'))
				->where($db->quoteName('id') . ' = ' . (int) $id);
			$db->setQuery($query);
			$exists = $db->loadResult();
		}

		if ($exists)
		{
			$fields     = [
				$db->quoteName('name') . ' = ' . $db->quote($messageContent['templateName']),
				$db->quoteName('type') . ' = ' . $db->quote($messageContent['templateType']),
				$db->quoteName('content') . ' = ' . $db->quote($messageContent['content']),
				$db->quoteName('wrapper') . ' = ' . $db->quote($messageContent['wrapper']),
				$db->quoteName('description') . ' = ' . $db->quote($messageContent['description']),
				$db->quoteName('hash') . ' = ' . $db->quote($templateHash),
				$db->quoteName('modified_date') . ' = ' . $db->quote(date('Y-m-d H:i:s'))
			];
			$conditions = [$db->quoteName('id') . ' = ' . (int) $id];
			$query->clear()
				->update($db->quoteName('#__semantyca_nm_templates'))
				->set($fields)
				->where($conditions);
			$db->setQuery($query);
			$db->execute();
			$parent_template_id = $id;
		}
		else
		{
			$columns = ['content', 'name', 'type', 'wrapper', 'description', 'hash'];
			$values  = [
				$db->quote($messageContent['content']),
				$db->quote($messageContent['templateName']),
				$db->quote($messageContent['templateType']),
				$db->quote($messageContent['wrapper']),
				$db->quote($messageContent['description']),
				$db->quote($templateHash)
			];
			$query->clear()
				->insert($db->quoteName('#__semantyca_nm_templates'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			try
			{
				$db->setQuery($query);
				$db->execute();
			}
			catch (Exception $e)
			{
				if (stripos($e->getMessage(), 'duplicate entry') !== false)
				{
					throw new DuplicatedEntityModelException(['A template with ' . $messageContent['name'] . ' name already exists.']);
				}
				else
				{
					throw $e;
				}
			}
			$parent_template_id = $db->insertid();
		}

		$query->clear()
			->delete($db->quoteName('#__semantyca_nm_custom_fields'))
			->where($db->quoteName('template_id') . ' = ' . (int) $parent_template_id);
		$db->setQuery($query);
		$db->execute();

		foreach ($messageContent['customFields'] as $customField)
		{
			$query   = $db->getQuery(true);
			$columns = ['template_id', 'name', 'type', 'caption', 'default_value', 'is_available'];
			$values  = [
				(int) $parent_template_id,
				$db->quote($customField['name']),
				(int) $customField['type'],
				$db->quote($customField['caption']),
				$db->quote($customField['defaultValue']),
				(int) $customField['isAvailable'],
			];
			$query->clear()
				->insert($db->quoteName('#__semantyca_nm_custom_fields'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			$db->setQuery($query);
			//error_log(str_replace('#_', 'smtc', ((string) $query)));
			$db->execute();
		}

		return true;
	}

	public function autoSaveTemplate($id, $data): bool
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->quoteName('#__semantyca_nm_templates'))
			->where('id = ' . (int) $id);
		$db->setQuery($query);
		$exists = $db->loadResult();

		if (!$exists)
		{
			throw new RecordNotFoundModelException(["The template with ID $id has not been found."]);
		}

		$query->clear();

		$fields = [];
		foreach ($data as $key => $value)
		{
			if (in_array($key, ['content', 'wrapper', 'description']))
			{
				$fields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
			}
		}

		if (empty($fields))
		{
			throw new Exception("No valid fields provided for update.");
		}

		$query->update($db->quoteName('#__semantyca_nm_templates'))
			->set($fields)
			->where('id = ' . (int) $id);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	public function delete(array $ids): bool
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$conditions = $db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $ids)) . ')';

		$query->delete($db->quoteName('#__semantyca_nm_templates'))
			->where($conditions);

		$db->setQuery($query);

		return $db->execute();
	}


}
