<?php

/**
 * carabus_get_path
 *
 * Returns the plugin path to a specified file.
 *
 * @param	string $filename The specified file.
 * @return	string
 */
function carabus_get_path( $filename = '' ) {
	return CARABUS_PATH . ltrim($filename, '/');
}

/*
 * carabus_include
 *
 * Includes a file within the CARABUS plugin.
 *
 * @param	string $filename The specified file.
 * @return	void
 */
function carabus_include( $filename = '' ) {
	$file_path = carabus_get_path($filename);
	if( file_exists($file_path) ) {
		include_once($file_path);
	}
}
