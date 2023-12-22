<?php

namespace Semantyca\Component\SemantycaNM\Administrator\Exception;

use Exception;

class MessagingException extends Exception
{
	private $errors;

	public function __construct(array $errors = [])
	{
		parent::__construct();
		$this->errors = $errors;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function toArray()
	{
		return $this->errors;
	}
}
