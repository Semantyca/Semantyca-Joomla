<?php

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

class TemplateDTO
{
	public $id;
	public $regDate;
	public $name;
	public $content;
	public $wrapper;
	public $customFields = [];

	public function toArray()
	{
		return [
			'id'           => $this->id,
			'name'         => $this->name,
			'content'      => $this->content,
			'wrapper'      => $this->wrapper,
			'customFields' => $this->customFields
		];
	}
}
