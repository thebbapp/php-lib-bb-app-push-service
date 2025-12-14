<?php

declare(strict_types=1);

namespace BbApp\PushService\Error;

/**
 * Thrown when push subscription validation fails.
 */
class PushSubscriptionValidationError extends \Error
{
	public $errorCode;
	public $status;

	/**
	 * Constructs a validation error with error code, message, and HTTP status.
	 *
	 * @param string $errorCode
	 * @param string $message
	 * @param int $status
	 */
	public function __construct(string $errorCode, string $message, int $status)
	{
		parent::__construct($message);
		$this->errorCode = $errorCode;
		$this->status = $status;
	}
}
