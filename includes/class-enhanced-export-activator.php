<?php

/**
 * Fired during plugin activation
 *
 * @link       https://alexdenche.dev
 * @since      1.0.0
 *
 * @package    Enhanced_Export
 * @subpackage Enhanced_Export/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Enhanced_Export
 * @subpackage Enhanced_Export/includes
 * @author     Alex Denche <daext3r@gmail.com>
 */
class Enhanced_Export_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		global $wpdb;

		$exports_table = $wpdb->prefix . 'ee_exports';
		$templates_table = $wpdb->prefix . 'ee_templates';
		$custom_fields_table = $wpdb->prefix . 'ee_custom_fields';

		$charset_collate = $wpdb->get_charset_collate();

		$exports_sql = "CREATE TABLE IF NOT EXISTS $exports_table (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				name tinytext NOT NULL,
				fields text,
				filters text,
				records bigint(20),
				processed bigint(20),
				file_name tinytext,
				status ENUM('ready', 'inprogress', 'completed', 'toconfigure') not null default 'toconfigure',
				PRIMARY KEY  (id)
			) $charset_collate;";


			$templates_sql = "CREATE TABLE $custom_fields_table (
				id mediumint(9) PRIMARY KEY AUTO_INCREMENT,
				name tinytext,
				`fields` text not null,
				date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				filters text not null 
				) $charset_collate;";

			$custom_fields_sql = "CREATE TABLE $custom_fields_table (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					name tinytext NOT NULL,
					query text,
					PRIMARY KEY (id)
				) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($exports_sql);
		dbDelta($custom_fields_sql);
		dbDelta($templates_sql);



	}

}
