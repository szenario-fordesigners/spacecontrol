# Release Notes for spacecontrol

## 5.2.0 - 2026-04-08
### Added
- Runtime state (disk usage, notification thresholds, initialization flag) is now stored in dedicated database tables (`spacecontrol_plugin_data`, `spacecontrol_file_sizes`) instead of Craft's plugin settings / project config
- New `SettingsService` for typed read/write access to the `spacecontrol_plugin_data` table
- New `FileScanningService` for recursive directory scanning with chunked DB upserts and staleness pruning
- Migration (`m260408_120000_migrate_settings_to_database`) to carry existing project-config values into the new tables on first run
### Improved
- Disk usage now reflects actual allocated disk space (`stat()['blocks'] * 512`) rather than logical file size
- Queue job TTR increased to 1800 s (30 minutes) to prevent long scans from being killed and retried prematurely
- Table names in `Install.php` use Craft's `{{%...}}` prefix syntax so installs with a custom `tablePrefix` work correctly
- Plugin instance null-guards added to `SettingsHelper::setValue()` and `setValues()` to prevent fatal errors during early bootstrap

## 5.1.3 - 2025-08-27
### Improved
- Prevent the Craft queue from locking up with spacecontrol jobs

## 5.1.2 - 2025-08-27
### Improved
- Cleaned up design
- More focused information
- Improved reliability
### Fixed
- Versioning fix

## 5.0.0 - 2024-06-07
### Added
- Craft 5 compatibility
## 1.3.2 - 2024-06-04
### Removed
- Craft 5 compatibility, will be added again

## 1.3.1 - 2024-06-04
### Added
- Craft 5 compatibility

### Improvements
- Better events for calculating used space

## 1.2.3 - 2023-11-27
### Fixed
- A bug where it was not possible to save settings

## 1.2.2 - 2023-11-24
### Fixed
- missing PRIMARY_SITE_URL throwing exceptions ([#10](https://github.com/szenario-fordesigners/spacecontrol/issues/10))

## 1.2.1 - 2023-11-24
### Fixed
- A bug where missing PRIMARY_SITE_URL throwing exception ([#10](https://github.com/szenario-fordesigners/spacecontrol/issues/10))

## 1.2.0 - 2023-11-23
### Added
- Email notification service

## 1.1.2 - 2023-10-26
### Fixed
- Widget output bug

## 1.1.1 - 2023-10-25
### Fixed
- Removed deprecated sprig.script tag

## 1.1.0 - 2023-09-14
### Added
- Option to include database size in space used ([#7](https://github.com/szenario-fordesigners/spacecontrol/issues/7))
### Fixed
- A bug where multiple queue jobs which calculate used space would run at the same time
- UI bug when widget was not initialized yet

## 1.0.2 - 2023-08-04
### Fixed
- Widget output

## 1.0.1 - 2023-08-01
### Fixed
- Widget Icon
- Issue when installing via Craft CLI

## 1.0.0 - 2023-07-27
- Initial release 🎉

