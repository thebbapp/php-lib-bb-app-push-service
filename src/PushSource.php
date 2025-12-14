<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\ContentSource\ContentSourceAbstract;

/**
 * Coordinates push notification generation from content source events.
 */
abstract class PushSource
{
	protected $push_queue;
	protected $push_subscription;
	protected $content_source;

	/**
	 * Constructs a push source with the given repositories and content source.
	 *
	 * @param PushQueueRepository $push_queue
	 * @param PushSubscriptionRepository $push_subscription
	 * @param ContentSourceAbstract $content_source
	 */
	public function __construct(
		PushQueueRepository $push_queue,
		PushSubscriptionRepository $push_subscription,
		ContentSourceAbstract $content_source
	) {
		$this->push_queue = $push_queue;
        $this->push_subscription = $push_subscription;
		$this->content_source = $content_source;
	}

	/**
	 * Extracts push notification data from the given content.
	 *
	 * @param mixed $content
	 * @return array
	 */
	public function get_push_notification_data($content): array
	{
		if (!$this->is_valid_content_for_notification($content)) {
			return [];
		}

		$targets = $this->build_push_service_targets_for_object($content);

		if (empty($targets)) {
			return [];
		}

		$subscriber_count = $this->push_subscription->count_subscribers_for_targets($targets);

		if ($subscriber_count === 0) {
			return [];
		}

		return array_filter(compact('targets') + [
			'user_id' => $this->get_user_id(),
			'guest_id' => $this->get_guest_id(),
			'object_type' => $this->get_object_type($content),
			'object_id' => $this->get_object_id($content)
		]);
	}

	/**
	 * Handles content insertion by enqueueing a push notification if appropriate.
	 *
	 * @param mixed $content
	 * @return void
	 */
	public function handle_content_insertion($content): void
	{
		$args = $this->get_push_notification_data($content);

		if (empty($args)) {
			return;
		}

		$this->push_queue->enqueue($args);
	}

	/**
	 * Retrieves content from the content source.
	 *
	 * @param string $object_type
	 * @param int $object_id
	 * @return mixed
	 */
	public function get_content(string $object_type, int $object_id)
	{
		return $this->content_source->get_content($object_type, $object_id);
	}

	/**
	 * Extracts the message text from the given content.
	 *
	 * @param string $content
	 * @return string
	 */
	abstract public function get_message_content(string $content): string;

	/**
	 * Extracts message data from the given object.
	 *
	 * @param mixed $object
	 * @return array
	 */
	abstract public function extract_message_data($object): array;

	/**
	 * Builds push service targets for the given object.
	 *
	 * @param mixed $object
	 * @return array
	 */
	abstract public function build_push_service_targets_for_object($object): array;

	/**
	 * Prepares a message envelope for the notification.
	 *
	 * @param string $content_type
	 * @param array $data
	 * @param bool $subtitles
	 * @return array
	 */
	abstract public function prepare_message_envelope(string $content_type, array $data, bool $subtitles): array;

	/**
	 * Registers push source hooks and handlers.
	 *
	 * @return void
	 */
	abstract public function register(): void;

	/**
	 * Checks if the content is valid for generating a notification.
	 *
	 * @param mixed $content
	 * @return bool
	 */
	abstract protected function is_valid_content_for_notification($content): bool;

	/**
	 * Gets the user ID associated with the content.
	 *
	 * @return int
	 */
	abstract protected function get_user_id(): int;

	/**
	 * Gets the guest ID associated with the content.
	 *
	 * @return string|null
	 */
	abstract protected function get_guest_id(): ?string;

	/**
	 * Gets the object type of the content.
	 *
	 * @param mixed $content
	 * @return string
	 */
	abstract protected function get_object_type($content): string;

	/**
	 * Gets the object ID of the content.
	 *
	 * @param mixed $content
	 * @return int
	 */
	abstract protected function get_object_id($content): int;
}
