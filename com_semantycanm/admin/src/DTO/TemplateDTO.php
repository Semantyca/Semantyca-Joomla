<?php

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

use DateTime;

class TemplateDTO
{
	public int $id;
	public DateTime $regDate;
	public string $name;
	public string $type;
	public string $description;
	public string $content;
	public string $wrapper;
	public array $customFields = [];

	public function toArray(): array
	{
		return [
			'id'           => $this->id,
			'regDate'      => $this->regDate->format('Y-m-d H:i:s'),
			'name'         => $this->name,
			'type'         => $this->type,
			'description'  => $this->description,
			'content'      => $this->content,
			'wrapper'      => $this->wrapper,
			'customFields' => array_map(function ($field) {
				return [
					'id'           => $field['id'],
					'name'         => $field['name'],
					'type'         => $field['type'],
					'caption'      => $field['caption'],
					'defaultValue' => $field['defaultValue'],
					'isAvailable'  => $field['isAvailable'],
				];
			}, $this->customFields)
		];
	}
}
