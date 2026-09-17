# Changelog

All notable changes to `Forms` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release
- `TranslatableInput` field: multi-language text/textarea whose value is an array keyed by locale, with a globe button that opens a dialog to edit every locale, per-locale validation via a `TranslationsRule` (errors reported as `name.en`, `name.ar`, `name.ckb`, ...) and locale metadata (native name and text direction) read from the panel's `config('app.available_locales')` list by default, so languages are defined once in `config/app.php`. An optional `laravilt-forms.locales` config key, shipped empty, overrides that list with plain codes or per-locale `name`/`direction`/`label` metadata when content languages differ from UI languages. Shipped for Blade, Vue and React.
- `required()` is now mirrored as a `required` validation rule on every field, so it is enforced even when other rules such as `maxLength()` are set (previously the schema only added it for fields with no other rules).

### Changed

### Deprecated

### Removed

### Fixed

### Security
