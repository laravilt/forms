![Forms](./arts/screenshot.jpg)

# Forms Plugin for Laravilt

[![Latest Stable Version](https://poser.pugx.org/laravilt/forms/version.svg)](https://packagist.org/packages/laravilt/forms)
[![License](https://poser.pugx.org/laravilt/forms/license.svg)](https://packagist.org/packages/laravilt/forms)
[![Downloads](https://poser.pugx.org/laravilt/forms/d/total.svg)](https://packagist.org/packages/laravilt/forms)
[![Dependabot Updates](https://github.com/laravilt/forms/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/laravilt/forms/actions/workflows/dependabot/dependabot-updates)
[![PHP Code Styling](https://github.com/laravilt/forms/actions/workflows/fix-php-code-styling.yml/badge.svg)](https://github.com/laravilt/forms/actions/workflows/fix-php-code-styling.yml)
[![Tests](https://github.com/laravilt/forms/actions/workflows/tests.yml/badge.svg)](https://github.com/laravilt/forms/actions/workflows/tests.yml)

Ready-to-use form components for Inertia.js, seamlessly managed by a PHP backend and Laravel, with advanced customization options. Fully compatible with FilamentPHP v4, this package supports all input types in the sleek reka-ui and shadcn-vue styles, providing a polished, modern solution for building dynamic, interactive forms.

## Installation

You can install the plugin via composer:

```bash
composer require laravilt/forms
```

The package will automatically register its service provider which handles all Laravel-specific functionality (views, migrations, config, etc.).

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="forms-config"
```

## Assets

Publish the plugin assets:

```bash
php artisan vendor:publish --tag="forms-assets"
```

## Testing

```bash
composer test
```

## Code Style

```bash
composer format
```

## Static Analysis

```bash
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
