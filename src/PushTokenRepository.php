<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\Result\Result;

/**
 * Manages push notification token storage and retrieval.
 */
abstract class PushTokenRepository
{
	/**
	 * Retrieves push tokens for the given targets, excluding specified user or guest.
	 *
	 * @param array $targets
	 * @param int $user_id
	 * @param string $guest_id
	 * @return array
	 */
	abstract public function get_tokens_for_targets(
		array $targets,
		int $user_id = 0,
		string $guest_id = ''
	): array;

	/**
	 * Updates the last active timestamp for the given token IDs.
	 *
	 * @param array $ids
	 * @return void
	 */
	abstract public function update_last_active_for_token_ids(
        array $ids
    ): void;

	/**
	 * Deletes tokens by their IDs.
	 *
	 * @param array $ids
	 * @return void
	 */
	abstract public function delete_tokens_by_ids(
        array $ids
    ): void;

	/**
	 * Retrieves an existing token record if it exists.
	 *
	 * @param string $service
	 * @param string $token
	 * @return object|null
	 */
	abstract public function get_existing_token(
        string $service,
        string $token
    ): ?object;

	/**
	 * Updates the last active timestamp and optionally binds a token to a user or guest.
	 *
	 * @param int $id
	 * @param int|null $user_id
	 * @param string|null $guest_id
	 * @param string $last_active_date_gmt
	 * @return void
	 */
	abstract public function update_last_active_and_bind_user(
        int $id,
        ?int $user_id,
        ?string $guest_id,
        string $last_active_date_gmt
    ): void;

	/**
	 * Counts the number of tokens for a user.
	 *
	 * @param int $user_id
	 * @return int
	 */
	abstract public function count_tokens_for_user(
        int $user_id
    ): int;

	/**
	 * Deletes the oldest token for a user.
	 *
	 * @param int $user_id
	 * @return void
	 */
	abstract public function delete_oldest_token_for_user(
        int $user_id
    ): void;

	/**
	 * Inserts a new push token.
	 *
	 * @param string $uuid
	 * @param int $user_id
	 * @param string|null $guest_id
	 * @param string $service
	 * @param string $token
	 * @param string $last_active_date_gmt
	 * @return Result<void,\Throwable>
	 */
	abstract public function insert_token(
        string $uuid,
        int $user_id,
        ?string $guest_id,
        string $service,
        string $token,
        string $last_active_date_gmt
    ): Result;

	/**
	 * Migrates all guest tokens to a user account.
	 *
	 * @param int $user_id
	 * @param string $guest_id
	 * @return Result<void,\Throwable>
	 */
	abstract public function migrate_guest_tokens_to_user(
        int $user_id,
        string $guest_id
    ): Result;

	/**
	 * Deletes a token by UUID and user ID.
	 *
	 * @param string $uuid
	 * @param int $user_id
	 * @return Result<void,\Throwable>
	 */
	abstract public function delete_token_by_uuid_user_id(
        string $uuid,
        int $user_id
    ): Result;

	/**
	 * Deletes a token by UUID and guest ID.
	 *
	 * @param string $uuid
	 * @param string $guest_id
	 * @return Result<void,\Throwable>
	 */
	abstract public function delete_token_by_uuid_guest_id(
        string $uuid,
        string $guest_id
    ): Result;

	/**
	 * Deletes all tokens for a guest ID.
	 *
	 * @param string $guest_id
	 * @return int
	 */
	abstract public function delete_tokens_by_guest_id(
        string $guest_id
    ): int;
}
