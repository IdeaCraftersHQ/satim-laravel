# Changelog

All notable changes to `ideacrafters/satim-laravel` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
