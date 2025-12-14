<?php

declare(strict_types=1);

namespace BbApp\PushService;

/**
 * Contains table names for push service database operations.
 */
class PushRepositoryTables
{
	public $subscriptions;
	public $tokens;
	public $queue;

	/**
	 * Constructs repository table names with optional prefix.
	 *
	 * @param string $prefix
	 * @param string $subscriptions
	 * @param string $tokens
	 * @param string $queue
	 */
	public function __construct(
		string $prefix = '',
		string $subscriptions = 'bb_app_push_subscriptions',
		string $tokens = 'bb_app_push_tokens',
		string $queue = 'bb_app_push_queue'
	) {
		$this->subscriptions = $prefix . $subscriptions;
		$this->tokens = $prefix . $tokens;
		$this->queue = $prefix . $queue;
	}
}
