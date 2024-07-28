<?php
/**
 * @package     SemantycaNM
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2024 Semantyca. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Semantyca\Component\SemantycaNM\Administrator\DTO;

use DateTime;
use JsonSerializable;

class NewsletterDTO implements JsonSerializable
{
	public ?int $id;
	public DateTime $regDate;
	public int $templateId;
	public string $templateName;
	public string $customFieldsValues;
	public array $articlesIds;
	public bool $isTest;
	public array $mailingListIds;
	public string $testEmail;
	public string $messageContent;
	public string $subject;
	public bool $useWrapper;

	public function toArray(): array
	{
		return [
			'id'                 => $this->id,
			'regDate'            => $this->regDate->format('Y-m-d H:i:s'),
			'templateId'         => $this->templateId,
			'templateName'       => $this->templateName,
			'customFieldsValues' => $this->customFieldsValues,
			'articlesIds'        => $this->articlesIds,
			'isTest'             => $this->isTest,
			'mailingList'        => $this->mailingListIds,
			'testEmail'          => $this->testEmail,
			'content'            => $this->messageContent,
			'subject'            => $this->subject,
			'useWrapper'         => $this->useWrapper,
		];
	}


	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
