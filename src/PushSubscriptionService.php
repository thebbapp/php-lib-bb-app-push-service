<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\ContentSource\ContentSourceAbstract;
use BbApp\PushService\Error\{
	PushSubscriptionNotFoundError,
	PushSubscriptionInvalidGuestIdError,
	PushSubscriptionInvalidUserIdError
};
use BbApp\Result\{Result, Success, Failure};

/**
 * Manages push notification subscription operations for users and guests.
 *
 * @var PushSubscriptionRepository $repository
 * @var ContentSourceAbstract $content_source
 */
abstract class PushSubscriptionService
{
	protected $repository;
	protected $content_source;

	/**
	 * Constructs the subscription service with the given repository and content source.
	 *
	 * @param PushSubscriptionRepository $repository
	 * @param ContentSourceAbstract $content_source
	 */
	public function __construct(
		PushSubscriptionRepository $repository,
		ContentSourceAbstract $content_source
	) {
		$this->repository = $repository;
		$this->content_source = $content_source;
	}

	/**
	 * Creates a user subscription if it doesn't already exist.
	 *
	 * @param int $user_id
	 * @param string $object_type
	 * @param int $object_id
	 * @return Result<void,\Throwable>
	 */
	public function create_user_subscription(
		int $user_id,
		string $object_type,
		int $object_id
	): Result {
		if ($this->repository->user_has_subscription($user_id, $object_type, $object_id)) {
			return new Success();
		}

		return $this->repository->create_user_subscription($user_id, $object_type, $object_id);
	}

	/**
	 * Creates a guest subscription if it doesn't already exist.
	 *
	 * @param string $guest_id
	 * @param string $object_type
	 * @param int $object_id
	 * @return Result<void,\Throwable>
	 */
	public function create_guest_subscription(
		string $guest_id,
		string $object_type,
		int $object_id
	): Result {
		if ($this->repository->guest_has_subscription($guest_id, $object_type, $object_id)) {
			return new Success();
		}

		return $this->repository->create_guest_subscription($guest_id, $object_type, $object_id);
	}

	/**
	 * Deletes a user subscription if it exists.
	 *
	 * @param int $user_id
	 * @param string $object_type
	 * @param int $object_id
	 * @return Result<void,\Throwable>
	 */
	public function delete_user_subscription(
		int $user_id,
		string $object_type,
		int $object_id
	): Result {
		if (!$this->repository->user_has_subscription($user_id, $object_type, $object_id)) {
			return new Failure(new PushSubscriptionNotFoundError());
		}

		return $this->repository->delete_user_subscription($user_id, $object_type, $object_id);
	}

	/**
	 * Deletes a guest subscription if it exists.
	 *
	 * @param string $guest_id
	 * @param string $object_type
	 * @param int $object_id
	 * @return Result<void,\Throwable>
	 */
	public function delete_guest_subscription(
		string $guest_id,
		string $object_type,
		int $object_id
	): Result {
		if (!$this->repository->guest_has_subscription($guest_id, $object_type, $object_id)) {
			return new Failure(new PushSubscriptionNotFoundError());
		}

		return $this->repository->delete_guest_subscription($guest_id, $object_type, $object_id);
	}

	/**
	 * Migrates all guest subscriptions to a user account after validation.
	 *
	 * @param int $user_id
	 * @param string $guest_id
	 * @return Result<void,\Throwable>
	 */
	public function migrate_guest_subscriptions_to_user(
		int $user_id,
		string $guest_id
	): Result {
		if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $guest_id)) {
			return new Failure(new PushSubscriptionInvalidGuestIdError());
		}

		if ($user_id <= 0) {
			return new Failure(new PushSubscriptionInvalidUserIdError());
		}

		return $this->repository->migrate_guest_subscriptions_to_user($user_id, $guest_id);
	}

	/**
	 * Validates a subscription request and returns the validated object details.
	 *
	 * @param PushSubscriptionValidateOptions $options
	 * @return Result<PushSubscriptionValidateResult,\Throwable>
	 */
	abstract public function validate_subscription(
		PushSubscriptionValidateOptions $options
	): Result;

	/**
	 * Initializes the subscription service.
	 *
	 * @return void
	 */
	abstract public function init(): void;

	/**
	 * Registers the subscription service hooks and handlers.
	 *
	 * @return void
	 */
	abstract public function register(): void;
}
