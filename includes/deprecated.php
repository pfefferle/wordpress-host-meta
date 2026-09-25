<?php
/**
 * Deprecated classes for backwards compatibility.
 *
 * Version 1.4.0 moved the `Host_Meta` class into the `Host_Meta` namespace.
 * The global class name is kept working here, so calls to the old static
 * methods keep working.
 *
 * @package Host_Meta
 */

/*
 * The alias is registered lazily, so a deprecation notice is triggered
 * the moment the legacy class name is actually referenced.
 */
\spl_autoload_register(
	function ( $class_name ) {
		if ( 'Host_Meta' !== $class_name ) {
			return;
		}

		\_deprecated_class( $class_name, '1.4.0', \Host_Meta\Host_Meta::class );

		\class_alias( \Host_Meta\Host_Meta::class, $class_name );
	}
);
