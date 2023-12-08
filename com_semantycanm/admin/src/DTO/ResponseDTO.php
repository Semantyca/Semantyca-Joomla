<?php

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

class ResponseDTO
{
	private $results;

	public function __construct($results = array())
	{
		$this->results = $results;
	}

	public function getResults()
	{
		return $this->results;
	}

	public function toArray()
	{
		return $this->results;
	}
}

