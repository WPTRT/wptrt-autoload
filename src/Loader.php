<?php
/**
 * Autoloader Class.
 *
 * A basic PSR-4 autoloader for theme developers.
 *
 * @author    WPTRT <themes@wordpress.org>
 * @copyright 2019 WPTRT
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link      https://github.com/WPTRT/wptrt-autoload
 */

namespace WPTRT\Autoload;

class Loader {

	/**
	 * Array of loaders.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $loaders = [];

	/**
	 * Adds a new prefix and path to load.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $prefix   Namespace prefix.
	 * @param  string  $path     Absolute path where to look for classes.
	 * @param  bool    $prepend  Whether to prepend the autoloader to the queue.
	 * @return void
	 */
	public function add( $prefix, $path, $prepend = false ) {

		$this->loaders[ $prefix ][ $path ] = [
			'prefix'  => $prefix,
			'path'    => $path,
			'prepend' => $prepend
		];
	}

	/**
	 * Removes a loader by prefix.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $prefix   Namespace prefix.
	 * @param  string  $path     Absolute path.
	 * @return void
	 */
	public function remove( $prefix, $path = '' ) {

		// Remove specific loader if both the prefix and path are provided.
		if ( $path ) {
			if ( $this->has( $prefix, $path ) ) {
				unset( $this->loaders[ $prefix ][ $path ] );
			}

			return;
		}

		// Remove all loaders for a prefix if no path is provided.
		if ( $this->has( $prefix ) ) {
			unset( $this->loaders[ $prefix ] );
		}
	}

	/**
	 * Checks if a loader is already added.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $prefix   Namespace prefix.
	 * @param  string  $path     Absolute path.
	 * @return bool
	 */
	public function has( $prefix, $path = '' ) {

		if ( $path ) {
			return isset( $this->loaders[ $prefix ] ) && isset( $this->loaders[ $prefix ][ $path ] );
		}

		return isset( $this->loaders[ $prefix ] );
	}

	/**
	 * Registers all loaders.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function register() {

		foreach ( $this->loaders as $collection ) {

			foreach ( $collection as $loader ) {

				spl_autoload_register( function( $class ) use ( $loader ) {

					$this->load( $class, $loader['prefix'], $loader['path'] );

				}, true, $loader['prepend'] );
			}
		}
	}

	/**
	 * Loads a class if it's within the given namespace.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $class    Fully-qualified class name.
	 * @param  string  $prefix   Namespace prefix.
	 * @param  string  $path     Absolute path where to look for classes.
	 * @return void
	 */
	protected function load( $class, $prefix, $path ) {

		// Bail if the class is not in our namespace.
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		// Remove the prefix from the class name.
		$class = str_replace( $prefix, '', $class );

		// Build the filename.
		$file = realpath( $path );
		$file = $file . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

		// If the file exists for the class name, load it.
		if ( file_exists( $file ) ) {
			include $file;
		}
	}
}
