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

use Exception;

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
	 * Array of prepend vs. append loaders.
	 *
	 * @since  1.1.0
	 * @access protected
	 * @var    bool[]
	 */
	protected $prepends = array(
		true  => 0,
		false => 0,
	);


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
				$this->prepends[ $this->loaders[ $prefix ][ $path ] ]--;
				unset( $this->loaders[ $prefix ][ $path ] );
			}

			return;
		}

		// Remove all loaders for a prefix if no path is provided.
		if ( $this->has( $prefix ) ) {
			$paths = $this->loaders[ $prefix ];
			foreach( $paths as $path ) {
				$this->prepends[ $paths[ $path ] ]--;
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
	 * @throw Exception
	 */
	public function register() {

		foreach( $this->prepends as $prepend => $added ) {
			if ( 0 === $added ) {
				continue;
			}
			try {

				spl_autoload_register( function( $class ) use ( $prepend ) {
					if ( 0 < $this->prepends[ $prepend ] ) {
						$this->load( $class, $prepend );
					}
				}, true, $prepend );

			} catch( Exception $e ) {

				error_log( $error = "Error attempting to register autoloader" );
				trigger_error( $error );

			}

		}

	}

	/**
	 * Loads a class if it's within the given namespace.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $class    Fully-qualified class name.
	 * @param  bool    $prepend  What this registered at the top or the bottom
	 * @return void
	 */
	protected function load( $class, $prepend ) {

		foreach( $this->loaders as $prefix => $paths ) {

			// Continue looking if class is not in our namespace.
			if ( 0 !== strpos( $class, $prefix ) ) {
				continue;
			}

			// Remove the prefix from the class name.
			$class = ltrim( preg_replace(
				'#^' . preg_quote( $prefix ) . '#',
				'',
				$class
			), '\\' );

			// Create a class name suffix
			$class = sprintf( '%s%s.php', DIRECTORY_SEPARATOR, $class );

			foreach( $paths as $path => $is_prepended ) {

				// If the prepends don't match
				if ( $is_prepended === (bool)$prepend ) {
					continue;
				}

				// We have no loaders prepended (if $prepend == true)
				// or appended (if $prepend == false)
				if (  0 === $this->prepends[ $prepend ] ) {
					continue;
				}

				// The file does not exist, continue looking
				if ( ! file_exists( $file = realpath( $path ) . $class ) ) {
					continue;
				}

				// If the file exists for the class name, load it and exit
				include $file;
				return;

			}

		}

	}

}
