<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\Result\Result;

/**
 * Manages the push notification queue for deferred processing.
 */
abstract class PushQueueRepository
{
	/**
	 * Enqueues a push notification for later processing.
	 *
	 * @param array $notification_data
	 * @return Result<void,\Throwable>
	 */
	abstract public function enqueue(array $notification_data): Result;

	/**
	 * Retrieves pending notifications from the queue.
	 *
	 * @param int $limit
	 * @return array
	 */
	abstract public function get_pending_notifications(int $limit = 100): array;

	/**
	 * Marks a queue item as currently being processed.
	 *
	 * @param int $queue_id
	 * @return Result<void,\Throwable>
	 */
	abstract public function mark_as_processing(int $queue_id): Result;

	/**
	 * Deletes a processed queue item.
	 *
	 * @param int $queue_id
	 * @return Result<void,\Throwable>
	 */
	abstract public function delete(int $queue_id): Result;

	/**
	 * Cleans up stale processing items that exceed the time limit.
	 *
	 * @param int $minutes
	 * @return int
	 */
	abstract public function cleanup_stale_processing(int $minutes = 5): int;
}
