<?php

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

use DateTime;
use JsonSerializable;

class NewsletterDTO implements JsonSerializable
{
	public int $id;
	public DateTime $regDate;
	public int $template_id;
	public string $customFieldsValues;
	public array $articlesIds;
	public bool $isTest;
	public array $mailingList;
	public string $testEmail;
	public string $messageContent;
	public string $wrapper;

	public function toArray(): array
	{
		return [
			'id'                 => $this->id,
			'regDate'            => $this->regDate->format('Y-m-d H:i:s'),
			'template_id'        => $this->template_id,
			'customFieldsValues' => $this->customFieldsValues,
			'articlesIds'        => $this->articlesIds,
			'isTest'             => $this->isTest,
			'mailingList'        => $this->mailingList,
			'testEmail'          => $this->testEmail,
			'content'            => $this->messageContent,
			'wrapper'            => $this->wrapper,
		];
	}


	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
