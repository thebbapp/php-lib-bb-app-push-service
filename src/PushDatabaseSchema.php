<?php

declare(strict_types=1);

namespace BbApp\PushService;

/**
 * Defines database schema for push notification tables.
 */
abstract class PushDatabaseSchema
{
    public $tables;
	public $charset_collate;

	/**
	 * Constructs the database schema with table names and charset.
	 *
	 * @param PushDatabaseSchemaTables $tables
	 * @param string $charset_collate
	 */
	public function __construct(
		PushDatabaseSchemaTables $tables,
		string $charset_collate
	) {
		$this->tables = $tables;
		$this->charset_collate = $charset_collate;
	}

	/**
	 * Generates the SQL to create the push tokens table.
	 *
	 * @return string
	 */
	public function create_table_push_tokens(): string
	{
		return "CREATE TABLE {$this->tables->tokens} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			uuid CHAR(36) CHARACTER SET ascii NOT NULL,
			user_id BIGINT UNSIGNED NULL,
			guest_id CHAR(36) CHARACTER SET ascii NULL,
			service VARCHAR(16) NOT NULL,
			token VARBINARY(255) NOT NULL,
			last_active_date_gmt DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_service_token (user_id, service, token),
			UNIQUE KEY guest_service_token (guest_id, service, token),
			KEY uuid (uuid),
			KEY user_id (user_id),
			KEY guest_id (guest_id)
		) {$this->charset_collate};";
	}

	/**
	 * Generates the SQL to create the push queue table.
	 *
	 * @return string
	 */
	public function create_table_push_queue(): string
	{
		return "CREATE TABLE {$this->tables->queue} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			notification_data TEXT NOT NULL,
			created_at DATETIME NOT NULL,
			status ENUM('pending','processing') NOT NULL DEFAULT 'pending',
			PRIMARY KEY (id),
			KEY status_created (status, created_at)
		) {$this->charset_collate};";
	}

	/**
	 * Generates the SQL to create the push subscriptions table.
	 *
	 * @return string
	 */
	public function create_table_push_subscriptions(): string
	{
		return "CREATE TABLE {$this->tables->subscriptions} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			guest_id CHAR(36) CHARACTER SET ascii NULL,
			object_type VARCHAR(20) CHARACTER SET ascii NOT NULL,
			object_id BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_object (user_id, object_type, object_id),
			UNIQUE KEY guest_object (guest_id, object_type, object_id),
			KEY user_id (user_id),
			KEY guest_id (guest_id),
			KEY object (object_type, object_id)
		) {$this->charset_collate};";
	}

	/**
	 * Installs the database schema.
	 *
	 * @return void
	 */
	abstract public function install(): void;

	/**
	 * Uninstalls the database schema.
	 *
	 * @return void
	 */
	abstract public function uninstall(): void;
}
