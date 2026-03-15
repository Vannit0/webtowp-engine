# WebToWP Engine

A powerful WordPress plugin with modular architecture for converting web content into WordPress content.

## Structure

```
webtowp-engine/
├── webtowp-engine.php          # Main plugin file
├── includes/                    # Core classes
│   └── class-webtowp-engine.php # Main singleton class
├── modules/                     # Feature modules
├── assets/                      # Static assets
│   ├── css/                    # Stylesheets
│   ├── js/                     # JavaScript files
│   └── images/                 # Images
└── languages/                   # Translation files
```

## Features

- **Singleton Pattern**: Ensures single instance of the main engine class
- **Autoloader**: Automatically loads classes from `/includes` and `/modules` directories
- **Modular Architecture**: Easy to extend with new modules
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **ACF Integration**: Pre-configured ACF field groups with REST API support
- **REST API**: Custom endpoints and automatic ACF field exposure
- **Automatic Updates**: GitHub-based update system for seamless plugin updates

## Constants

- `W2WP_VERSION` - Plugin version
- `W2WP_PATH` - Plugin directory path
- `W2WP_URL` - Plugin directory URL
- `W2WP_BASENAME` - Plugin basename
- `W2WP_INCLUDES_PATH` - Includes directory path
- `W2WP_MODULES_PATH` - Modules directory path
- `W2WP_ASSETS_PATH` - Assets directory path
- `W2WP_ASSETS_URL` - Assets directory URL

## Usage

The plugin initializes automatically. Access the main instance:

```php
$engine = webtowp_engine();
```

## Adding New Classes

Classes are autoloaded from `/includes` and `/modules` directories. Follow this naming convention:

- Class name: `WebToWP_Feature_Name`
- File name: `class-webtowp-feature-name.php`

## Requirements

- WordPress 5.8+
- PHP 7.4+
- ACF (Advanced Custom Fields) - Free or Pro
- Plugin Update Checker library (optional, for automatic updates)

## Installation

1. Upload the plugin to `/wp-content/plugins/webtowp-engine/`
2. Activate the plugin through WordPress admin
3. Install ACF plugin if not already installed
4. **(Optional)** For automatic updates from GitHub, install Plugin Update Checker:
   - Download from: https://github.com/YahnisElsts/plugin-update-checker/releases
   - Extract to `includes/plugin-update-checker/`
   - See [UPDATES.md](UPDATES.md) for detailed instructions

## Automatic Updates

This plugin supports automatic updates from GitHub. See [UPDATES.md](UPDATES.md) for:
- Installation instructions for Plugin Update Checker
- How to create releases
- Troubleshooting guide

## License

GPL v2 or later
