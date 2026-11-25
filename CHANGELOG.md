# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Add `UPGRADED.md` with the upgrade procedure from Drupal 9 to Drupal 11.

### Fixed

- Enable the `text_with_summary` module in the configuration. Drupal 11.5 needs it for the `node.body` field.

## [3.0.0] - 2026-11-23

### Added

- Add the development tools and the GitHub Actions workflows for the coding standards checks.

### Changed

- Upgrade to Drupal 11.4.x.
- Upgrade the `field_group` module to 4.x.
- Change the base theme of `bibbox_theme` from `adminimal_theme` to `claro`.
- Change the PHP docker images to PHP 8.5.
- Change to the maintained `nginx/nginx-unprivileged` image.
- Apply the coding standards to the custom code.
- Change the change log format to Keep a Changelog.

### Removed

- Remove the `adminimal_theme` theme.

### Fixed

- Correct the site install from the existing configuration.

## [2.0.0] - 2026-06-08

### Changed

- Upgrade to Drupal 10.4.x.

## [1.6.1] - 2020-09-28

### Changed

- Upgrade to Drupal 8.9.6.

## [1.4.0] - 2019-11-15

### Changed

- Updated to Drupal 8.7.10.

## [1.2.2] - 2018-10-22

### Changed

- Updated to Drupal 8.6.2.

## [1.2.1] - 2018-09-17

### Added

- Added clear printer queue command.

## [1.2.0] - 2018-03-21

### Added

- Added new config options.

### Changed

- Drupal update.

## [1.1.0] - 2017-12-19

### Changed

- Change brand and logo to use image fields.

## [1.0.4] - 2017-08-03

### Fixed

- Fixed "Restart UI" and "Set out of order" actions.

## [1.0.3] - 2017-07-12

### Added

- Added bulk operations to machines view.

### Changed

- Updated drupal to 8.3.3.

## [1.0.2] - 2016-12-05

### Added

- Added out of order button.

### Changed

- Updated pager to show 100 elements og full.

## [1.0.1] - 2016-11-30

### Added

- Added reboot operation.

## [1.0.0] - 2016-11-30

### Added

- First version.

[Unreleased]: https://github.com/bibboxen/bibbox-admin/compare/3.0.0...HEAD
[3.0.0]: https://github.com/bibboxen/bibbox-admin/compare/2.0.0...3.0.0
[2.0.0]: https://github.com/bibboxen/bibbox-admin/compare/1.6.1...2.0.0
[1.6.1]: https://github.com/bibboxen/bibbox-admin/compare/1.4.0...1.6.1
[1.4.0]: https://github.com/bibboxen/bibbox-admin/compare/1.2.2...1.4.0
[1.2.2]: https://github.com/bibboxen/bibbox-admin/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/bibboxen/bibbox-admin/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/bibboxen/bibbox-admin/compare/v1.1.0...1.2.0
[1.1.0]: https://github.com/bibboxen/bibbox-admin/compare/v1.0.4...v1.1.0
[1.0.4]: https://github.com/bibboxen/bibbox-admin/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/bibboxen/bibbox-admin/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/bibboxen/bibbox-admin/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/bibboxen/bibbox-admin/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/bibboxen/bibbox-admin/releases/tag/v1.0.0
