<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\Result\{Result, Failure};
use Exception;

/**
 * Processes queued push notifications and dispatches them to subscribers.
 */
abstract class PushQueueService
{
	private $event_handler;
	private $repository;

	/**
	 * Constructs the queue service with the given repository and coordinator.
	 *
	 * @param PushQueueRepository $repository
	 * @param PushNotificationCoordinator $coordinator
	 */
	public function __construct(
		PushQueueRepository $repository,
		PushNotificationCoordinator $coordinator
	) {
        $this->repository = $repository;
		$this->event_handler = $coordinator;
	}

	/**
	 * Processes pending push notifications from the queue.
	 *
	 * @return void
	 */
	public function process_queue(): void
	{
		$this->repository->cleanup_stale_processing();

		$notifications = $this->repository->get_pending_notifications(50);

		if (empty($notifications)) {
			return;
		}

		foreach ($notifications as $notification) {
            $result = $this->process_queue_item($notification);

            if ($result instanceof Failure) {
                error_log($result->unwrap()->getMessage());
            }
		}
	}

	/**
	 * Processes a single queue item and handles the notification event.
	 *
	 * @param mixed $notification
	 * @return Result
	 */
    public function process_queue_item($notification): Result
    {
        $queue_id = (int) $notification['queue_id'];
        $mark_result = $this->repository->mark_as_processing($queue_id);

        if ($mark_result instanceof Failure) {
            return $mark_result;
        }

        try {
            $this->event_handler->handle_scheduled_event($notification);
        } catch (Exception $error) {
            return new Failure($error);
        }

        return $this->repository->delete($queue_id);
    }

	/**
	 * Initializes the queue service.
	 *
	 * @return void
	 */
	abstract public function init(): void;

	/**
	 * Deallocates resources and cleans up the queue service.
	 *
	 * @return void
	 */
    abstract public function dealloc(): void;
}
