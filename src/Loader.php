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
	 * Maintains the count of loaders to prepend/append.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $prepends = [
		true  => 0,
		false => 0
	];

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

		$this->loaders[ $prefix ][ $path ] = $prepend;

		$this->prepends[ $prepend ]++;
	}

	/**
	 * Removes a loader by prefix or prefix + path.
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

				$this->prepends[ $this->loaders[ $prefix ][ $path ] ]--;

				unset( $this->loaders[ $prefix ][ $path ] );
			}

			return;
		}

		// Remove all loaders for a prefix if no path is provided.
		if ( $this->has( $prefix ) ) {

			foreach ( $this->loaders[ $prefix ] as $path ) {
				$this->prepends[ $this->loaders[ $prefix ][ $path ] ]--;
			}

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

		foreach ( $this->prepends as $prepend => $count ) {

			// Only register if there is at least one loader.
			if ( 0 < $count ) {

				spl_autoload_register( function( $class ) use ( $prepend ) {

					$this->load( $class, $prepend );

				}, true, $prepend );
			}
		}
	}

	/**
	 * Loads a class if it's within the given namespace.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @param  string  $class    Fully-qualified class name.
	 * @param  bool    $prepend  Whether the autoloader is appended/prepended.
	 * @return void
	 */
	protected function load( $class, $prepend ) {

		foreach ( $this->loaders as $prefix => $paths ) {

			// Continue if the class is not in our namespace.
			if ( 0 !== strpos( $class, $prefix ) ) {
				continue;
			}

			// Remove the prefix from the class name.
			$class = ltrim( str_replace( $prefix, '', $class ), '\\' );
			$class = DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

			// Loop through the paths to see if we can find the file
			// for the class.
			foreach ( $paths as $path => $is_prepended ) {

				// Continue if prepends don't match.
				if ( $is_prepended !== (bool) $prepend ) {
					continue;
				}

				// Load the class file if it exists and return.
				if ( file_exists( $file = realpath( $path ) . $class ) ) {
					include $file;
					return;
				}
			}
		}
	}
}
