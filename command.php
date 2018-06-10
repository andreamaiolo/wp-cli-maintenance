<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Enable or disable the maintenance mode
 *
 * @when after_wp_load
 */
$maintenance_command = function() {
	try {
		$wp_config_path = ABSPATH . 'wp-config.php';

		$config_transformer = new WPConfigTransformer( $wp_config_path );

		$config_type = 'constant';
		$config_name = 'MAINTENANCE';

		$config_transformer_options = array(
			'raw' => true,
		);

		$success_message = 'Maintenance mode ';

		if ( $config_transformer->exists( $config_type, $config_name ) ) {
			$config_value = filter_var( $config_transformer->get_value( $config_type, $config_name ), FILTER_VALIDATE_BOOLEAN );

			$config_transformer->update( $config_type, $config_name, $config_value ? 'false' : 'true', $config_transformer_options );

			$config_value = ! $config_value;
		} else {
			$config_value = true;

			$config_transformer->add( $config_type, $config_name, var_export( $config_value, true ), $config_transformer_options );
		}

		$success_message .= ( $config_value ? 'en' : 'dis' ) . 'abled';
	} catch ( Exception $e ){
		WP_CLI::error( $e->getMessage() );
	}

	$maintenance_path = get_stylesheet_directory() . '/maintenance/maintenance.php';

	if ( ! file_exists( $maintenance_path ) ) {
		global $wp_filesystem;

		WP_Filesystem();

		$wp_filesystem->mkdir( dirname( $maintenance_path ) );

		if ( ! $wp_filesystem->put_contents( $maintenance_path, $wp_filesystem->get_contents( __DIR__ . '/maintenance.stub' ) ) ) {
			WP_CLI::error( "Error creating file: $maintenance_path" );
		} else {
			WP_CLI::log( 'Created: ' . $maintenance_path );
		}
	}

	WP_CLI::success( $success_message );
};

WP_CLI::add_command( 'maintenance', $maintenance_command );
