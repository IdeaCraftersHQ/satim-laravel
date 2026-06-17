# Changelog

All notable changes to `ideacrafters/satim-laravel` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.3.0] - 2026-06-17

### Added
- Support for Laravel 13.x (`illuminate/http` and `illuminate/support` `^13.0`).

### Changed
- Widened dev dependencies: `pestphp/pest` `^4.0`, `orchestra/testbench` `^11.0`, `phpunit/phpunit` `^12.0`.

## [1.2.2] - 2026-06-02

### Fixed
- Guard against null/empty gateway responses in `SatimClient::register/confirm/refund`. Previously an empty body or non-JSON payload from SATIM (timeout, partial outage) surfaced as an uncaught `TypeError`; it now throws a catchable `SatimException` with the operation name and body excerpt.

## [1.2.1] - 2026-03-12

### Fixed
- **`udf1` now always present in SATIM payment requests** — replaced `array_filter()` with a null-only filter (`fn($v) => $v !== null`) in `RegisterOrderData::toArray()`, preventing falsy string values (e.g. `"0"`) from being silently stripped from `jsonParams`
- Fixed constructor parameter order in `RegisterOrderData` — `udf1` (required) was incorrectly placed after optional parameters, which is deprecated in PHP 8.1+

### Changed
- `udf1` is now enforced as a required field at both the `Satim` orchestration layer and the `RegisterOrderData` DTO level, satisfying SATIM certification requirements

### Tests
- Replaced stale "accepts null for udf1" test with "throws exception when udf1 is not provided"
- Updated "accepts null for optional udf1-5 fields" test to cover only udf2-5 (udf1 is required)
- Added test: `toArray always includes udf1 in jsonParams even with numeric-only value`
- Added test: `throws exception when udf1 is not provided` in `SatimTest`
- Added test: `register includes udf1 in jsonParams sent to SATIM`

## [1.2.0] - 2025-12-25

### Changed
- **BREAKING**: Removed automatic order number generation - `orderNumber` is now required
- Stricter validation for `orderNumber` - must be alphanumeric only (A-Z, a-z, 0-9)
- Stricter validation for `currency` - must be 3-digit ISO 4217 code (e.g., "012")
- Stricter validation for `terminalId` - must be alphanumeric (1-16 characters)
- Stricter validation for UDF1-5 fields - must be alphanumeric if provided
- Language field now normalized to uppercase (FR, EN, AR)
- All input fields now trimmed before validation
- Description length validation now uses mb_strlen for proper multibyte character support
- Expand PHP support to 8.1+ (previously 8.2+)
- Add Laravel 10 and 11 compatibility (previously Laravel 12 only)
- Update test dependencies to support Laravel 10-12
- Improved error messages for validation failures

### Added
- Maximum amount validation (20 digits max)
- Comprehensive test coverage for all validation edge cases
- Consolidated validation logic for all UDF fields (udf1-5)

### Removed
- `GeneratesOrderNumbers` trait - order numbers must now be provided explicitly

## [1.1.0] - 2025-12-24

### Fixed
- Add missing errorMessage parameter in RegisterOrderResponse::fromArray()

## [1.0.0] - 2025-12-23

### Added
- Initial release
- Payment registration with Satim gateway
- Payment confirmation/status checking
- Refund processing
- Automatic amount conversion (DA to cents)
- Automatic order number generation
- Fluent/chainable API for building payment requests
- Comprehensive validation for all DTOs
- Support for custom user-defined fields (UDF1-UDF5)
- Support for French, English, and Arabic languages
- Custom exception types for better error handling (SatimException, SatimAuthenticationException, SatimPaymentException)
- Full test coverage with Pest PHP
- Interface-based design (SatimInterface)
- Laravel service provider and facade integration

### Security
- SSL verification enabled by default for all API requests
- Secure credential handling through environment variables
- No sensitive data logged or stored in code
