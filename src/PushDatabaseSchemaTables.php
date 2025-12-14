<?php

declare(strict_types=1);

namespace BbApp\PushService;

/**
 * Contains table names for push service database schema.
 */
class PushDatabaseSchemaTables
{
	public $queue;
	public $subscriptions;
	public $tokens;

	/**
	 * Constructs schema table names with optional prefix.
	 *
	 * @param string $prefix
	 * @param string $queue
	 * @param string $tokens
	 * @param string $subscriptions
	 */
	public function __construct(
        string $prefix = '',
		string $queue = 'bb_app_push_queue',
		string $tokens = 'bb_app_push_tokens',
		string $subscriptions = 'bb_app_push_subscriptions'
	) {
		$this->queue = $prefix . $queue;
		$this->subscriptions = $prefix . $subscriptions;
		$this->tokens = $prefix . $tokens;
	}
}
