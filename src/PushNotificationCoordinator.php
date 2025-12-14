<?php

declare(strict_types=1);

namespace BbApp\PushService;

/**
 * Coordinates the processing and delivery of queued push notifications.
 */
class PushNotificationCoordinator
{
	private $repository;
	private $source;

	/**
	 * Constructs the coordinator with the given repository and source.
	 *
	 * @param PushTokenRepository $repository
	 * @param PushSource $source
	 */
	public function __construct(
		PushTokenRepository $repository,
		PushSource $source
	) {
		$this->repository = $repository;
		$this->source = $source;
	}

	/**
	 * Handles a scheduled push notification event by dispatching to subscribed tokens.
	 *
	 * @param array $args
	 * @return void
	 */
	public function handle_scheduled_event(array $args): void
	{
		$object_type = isset($args['object_type']) ? (string) $args['object_type'] : '';
		$object_id = isset($args['object_id']) ? (int) $args['object_id'] : 0;
		$user_id = isset($args['user_id']) ? (int) $args['user_id'] : 0;
		$guest_id = isset($args['guest_id']) ? (string) $args['guest_id'] : '';

		if ($object_id <= 0) {
			return;
		}

		$content = $this->source->get_content($object_type, $object_id);

		if (empty($content)) {
			return;
		}

		$targets = $this->source->build_push_service_targets_for_object($content);

		if (empty($targets)) {
			return;
		}

		$tokens = $this->repository->get_tokens_for_targets($targets, $user_id, $guest_id);

		if (empty($tokens)) {
			return;
		}

		$message_data = $this->source->extract_message_data($content);

		$success_ids = [];
		$invalid_ids = [];
		$tokens_by_service = [];

		foreach ($tokens as $token) {
			if (!isset($tokens_by_service[$token['service']])) {
				$tokens_by_service[$token['service']] = [];
			}

			$tokens_by_service[$token['service']][] = $token;
		}

		foreach ($tokens_by_service as $service => $service_tokens) {
			if (!($transport = PushTransportAbstract::locate($service))) {
				continue;
			}

			$envelope = $this->source->prepare_message_envelope(
				$object_type,
				$message_data,
				$transport->message_envelope_subtitle
			);

			$result = $transport->handle_scheduled_event(
				$service_tokens,
				$envelope,
				$object_type,
				$object_id
			);

			$success_ids = array_merge($success_ids, $result['success_ids']);
			$invalid_ids = array_merge($invalid_ids, $result['invalid_ids']);
		}

		$success_ids = array_values(array_unique($success_ids));
		$invalid_ids = array_values(array_unique($invalid_ids));

		$this->repository->update_last_active_for_token_ids($success_ids);
		$this->repository->delete_tokens_by_ids($invalid_ids);
	}
}
