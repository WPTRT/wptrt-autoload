# WPTRT Autoload

A PSR-4 autoloader for WordPress themes.  Primarily, this repository exists for theme authors who want to use autoloading but aren't yet on something such as Composer.

Any classes loaded via this autoloader must follow the [PSR-4: Autoloading](https://www.php-fig.org/psr/psr-4/) standard for naming their namespaces, classes, and directories.

## Usage

Here's a real-world example of loading the [WPTRT Customize Section Button](https://github.com/WPTRT/customize-section-button) package:

```php
// Include the Loader class.
require_once get_theme_file_path( 'path/to/autoload/src/Loader.php' );

// Create a new instance of the Loader class.
$themeslug_loader = new \WPTRT\Autoload\Loader();

// Add (one or multiple) namespaces and their paths.
$themeslug_loader->add( 'WPTRT\\Customize\\Section\\', get_theme_file_path( 'path/to/customize-section-button/src' ) );

// Register all loaders.
$themeslug_loader->register();
```

### Loader::add() method

Primarily, theme authors would utilize the `add()` method to add a loader.  You can call `add()` multiple times to register multiple loaders.

```php
$themeslug_loader->add( $prefix, $path, $prepend = false );
```

* `$prefix` - This should be the namespace of the project.  Make sure to escape backslashes like `\\` instead of a single `\`.
* `$path` - This should be the absolute path to the source code of where the classes are housed.
* `$prepend` - Whether to prepend or append a particular loader to the autoload queue.  `false` by default.
