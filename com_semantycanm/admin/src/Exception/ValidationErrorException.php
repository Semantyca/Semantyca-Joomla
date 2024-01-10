<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Exception;

use Exception;

class ValidationErrorException extends Exception
{
	private array $errors;

	public function __construct(array $errors = [], $message = "")
	{
		parent::__construct($message);
		$this->errors = $errors;
	}

	public function getErrors(): array
	{
		return $this->errors;
	}

	public function toArray(): array
	{
		return $this->errors;
	}
}
