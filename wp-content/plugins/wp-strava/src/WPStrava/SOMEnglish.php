<?php

/**
 * SOM English class.
 *
 * All conversions are limited to 2 decimal places.
 */
class WPStrava_SOMEnglish extends WPStrava_SOM {

	/**
	 * Change meters to miles.
	 *
	 * @param float|string $m Distance in meters.
	 * @return string Distance in miles.
	 */
	public function distance( $m ) {
		return number_format_i18n( $m / 1609.344, 2 );
	}

	/**
	 * Change miles to meters.
	 *
	 * @param float $dist Distance in miles.
	 * @return float Distance in meters.
	 */
	public function distance_inverse( $dist ) {
		return (float) number_format( $dist * 1609.344, 2, '.', '' );
	}

	/**
	 * Abbreviated label for this system of measure's distance - Miles: mi.
	 *
	 * @return string 'mi.'
	 */
	public function get_distance_label() {
		return __( 'mi.', 'wp-strava' );
	}

	/**
	 * Change meters per second to miles per hour.
	 *
	 * @param float|string $mps Meters per second.
	 * @return string Miles per hour.
	 */
	public function speed( $mps ) {
		return number_format_i18n( $mps * 2.2369, 2 );
	}

	/**
	 * Abbreviated label for this system of measure's speed - Miles Per Hour: mph
	 *
	 * @return string 'mph'
	 */
	public function get_speed_label() {
		return __( 'mph', 'wp-strava' );
	}

	/**
	 * Change meters per second to minutes per mile.
	 *
	 * @param float|string $mps Meters per second.
	 * @return string Minutes Per Mile.
	 */
	public function pace( $mps ) {

		if ( ! $mps ) {
			return __( 'N/A', 'wp-strava' );
		}

		$mph = $mps * 2.2369;
		$s   = 3600 / $mph;
		$ss  = $s / 60;
		$ms  = floor( $ss ) * 60;
		$sec = sprintf( '%02d', round( $s - $ms ) );
		$min = floor( $ss );

		return "{$min}:{$sec}";
	}

	/**
	 * Abbreviated label for this system of measure's pace - Minutes Per Mile: min/mile
	 *
	 * @return string 'min/mile'
	 */
	public function get_pace_label() {
		return __( 'min/mile', 'wp-strava' );
	}

	/**
	 * Change meters to feet.
	 *
	 * @param float|string $m Elevation in meters.
	 * @return string Elevation in feet.
	 */
	public function elevation( $m ) {
		return number_format_i18n( $m / 0.3048, 2 );
	}

	/**
	 * Abbreviated label for this system of measure's elevation - Feet: ft.
	 *
	 * @return string 'ft.'
	 */
	public function get_elevation_label() {
		return __( 'ft.', 'wp-strava' );
	}
}
