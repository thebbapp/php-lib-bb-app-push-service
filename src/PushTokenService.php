<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\PushService\Error\{
	PushServiceNotFoundError,
	PushTokenServiceInvalidGuestIdError,
	PushTokenServiceInvalidUserIdError
};
use BbApp\Result\{Result, Success, Failure};

/**
 * Manages push notification token registration and updates.
 */
abstract class PushTokenService
{
	public static $registry = [];

	protected $repository;

	/**
	 * Constructs the token service with the given repository.
	 *
	 * @param PushTokenRepository $repository
	 */
	public function __construct(PushTokenRepository $repository) {
		$this->repository = $repository;
	}

	/**
	 * Submits a push token for registration or update.
	 *
	 * @param int $current_user_id
	 * @param string $service
	 * @param string $token
	 * @param string|null $guest_id
	 * @param string $uuid
	 * @return Result<string,\Throwable>
	 */
	public function submit_push_token(
		int $current_user_id,
		string $service,
		string $token,
		?string $guest_id,
		string $uuid
	): Result {
		if (!($transport = PushTransportAbstract::locate($service))) {
			return new Failure(new PushServiceNotFoundError($service));
		}

		$result = $transport->validate_push_token($token);

		if ($result instanceof Failure) {
			return $result;
		}

		$bin_token = $transport->encode_push_token($token);
		$existing = $this->repository->get_existing_token($service, $bin_token);
		$now_gmt = gmdate('Y-m-d H:i:s');

		if ($existing) {
			$existing_user_id = isset($existing->user_id) ? (int) $existing->user_id : 0;
			$existing_guest_id = isset($existing->guest_id) ? (string) $existing->guest_id : null;

			$bind_user_id = ($current_user_id > 0 && $existing_user_id !== $current_user_id) ? $current_user_id : null;
			$bind_guest_id = ($guest_id !== null && $existing_guest_id !== $guest_id) ? $guest_id : null;

			$this->repository->update_last_active_and_bind_user((int) $existing->id, $bind_user_id, $bind_guest_id, $now_gmt);

			return new Success($existing->uuid);
		}

		$count = $this->repository->count_tokens_for_user($current_user_id);

		if ($count >= 100) {
			$this->repository->delete_oldest_token_for_user($current_user_id);
		}

		$insert_result = $this->repository->insert_token($uuid, $current_user_id, $guest_id, $service, $bin_token, $now_gmt);

		if ($insert_result instanceof Failure) {
			return $insert_result;
		}

		return new Success($uuid);
	}

	/**
	 * Migrates all guest tokens to a user account after validation.
	 *
	 * @param int $user_id
	 * @param string $guest_id
	 * @return Result<void,\Throwable>
	 */
	public function migrate_guest_tokens_to_user(
		int $user_id,
		string $guest_id
	): Result {
		if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $guest_id)) {
			return new Failure(new PushTokenServiceInvalidGuestIdError());
		}

		if ($user_id <= 0) {
			return new Failure(new PushTokenServiceInvalidUserIdError());
		}

		return $this->repository->migrate_guest_tokens_to_user($user_id, $guest_id);
	}

	/**
	 * Deletes a push token by UUID and user ID.
	 *
	 * @param string $uuid
	 * @param int $user_id
	 * @return Result<void,\Throwable>
	 */
	public function delete_token_by_uuid_user_id(
		string $uuid,
		int $user_id
	): Result {
		return $this->repository->delete_token_by_uuid_user_id($uuid, $user_id);
	}

	/**
	 * Deletes a push token by UUID and guest ID.
	 *
	 * @param string $uuid
	 * @param string $guest_id
	 * @return Result<void,\Throwable>
	 */
	public function delete_token_by_uuid_guest_id(
		string $uuid,
		string $guest_id
	): Result {
		return $this->repository->delete_token_by_uuid_guest_id($uuid, $guest_id);
	}

	/**
	 * Initializes the token service.
	 *
	 * @return void
	 */
	abstract public function init(): void;

	/**
	 * Registers the token service hooks and handlers.
	 *
	 * @return void
	 */
	abstract public function register(): void;
}
