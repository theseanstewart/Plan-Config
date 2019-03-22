#Changelog

All Notable changes to this package` will be documented in this file

## Version 1.4

- Significant refactor. The 2nd argument for the `plan()` helper function now allows for passing a $user object.
- Removed the following methods
    - **getPlanOfUser**
    - **getUserPlan**
    - **getAllowedOverrides**
    - **getPlanOverrides**

## Version 1.3

### Added

- Added new way to override a plan's config using an attribute on the user model. Requires updating app/config/plans.php with new section (see README).

## Version 1.2

### Added

- Unit tests along with travis ci 
- Added optional Facade to use
- Refactored code so its easier to test
- Added new methods