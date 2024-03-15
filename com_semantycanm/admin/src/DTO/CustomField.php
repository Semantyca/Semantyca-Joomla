<?php

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

class CustomField
{
	public string $name = "DefaultName";
	public string $type = "DefaultType";
	private string $caption = "DefaultCaption";

	public function __construct(string $name = "DefaultName", string $type = "DefaultType")
	{
		$this->name = $name;
		$this->type = $type;
		$this->updateCaption();
	}

	private function updateCaption()
	{
		$this->caption = "This is a {$this->type} with the name {$this->name}.";
	}

	public function getCaption(): string
	{
		return $this->caption;
	}

	public function setName(string $name)
	{
		$this->name = $name;
		$this->updateCaption();
	}

	public function setType(string $type)
	{
		$this->type = $type;
		$this->updateCaption();
	}
}