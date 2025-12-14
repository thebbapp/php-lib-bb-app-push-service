<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\Result\Result;

/**
 * Manages push notification subscriptions for users and guests.
 */
abstract class PushSubscriptionRepository
{
	/**
	 * Checks if a user has a subscription for the given object.
	 *
	 * @param int $user_id
	 * @param string $object_type
	 * @param int $object_id
	 * @return bool
	 */
    abstract public function user_has_subscription(int $user_id, string $object_type, int $object_id): bool;

    /**
     * Creates a push subscription for a user.
     *
     * @param int $user_id
     * @param string $object_type
     * @param int $object_id
     * @return Result<void,\Throwable>
     */
    abstract public function create_user_subscription(int $user_id, string $object_type, int $object_id): Result;

	/**
	 * Checks if a guest has a subscription for the given object.
	 *
	 * @param string $guest_id
	 * @param string $object_type
	 * @param int $object_id
	 * @return bool
	 */
    abstract public function guest_has_subscription(string $guest_id, string $object_type, int $object_id): bool;

    /**
     * Creates a push subscription for a guest.
     *
     * @param string $guest_id
     * @param string $object_type
     * @param int $object_id
     * @return Result<void,\Throwable>
     */
    abstract public function create_guest_subscription(string $guest_id, string $object_type, int $object_id): Result;

    /**
     * Deletes a push subscription for a user.
     *
     * @param int $user_id
     * @param string $object_type
     * @param int $object_id
     * @return Result<void,\Throwable>
     */
    abstract public function delete_user_subscription(int $user_id, string $object_type, int $object_id): Result;

    /**
     * Deletes a push subscription for a guest.
     *
     * @param string $guest_id
     * @param string $object_type
     * @param int $object_id
     * @return Result<void,\Throwable>
     */
    abstract public function delete_guest_subscription(string $guest_id, string $object_type, int $object_id): Result;

    /**
     * Migrates all guest subscriptions to a user account.
     *
     * @param int $user_id
     * @param string $guest_id
     * @return Result<void,\Throwable>
     */
    abstract public function migrate_guest_subscriptions_to_user(int $user_id, string $guest_id): Result;

	/**
	 * Counts the number of subscribers for the given targets.
	 *
	 * @param array $targets
	 * @return int
	 */
    abstract public function count_subscribers_for_targets(array $targets): int;
}
