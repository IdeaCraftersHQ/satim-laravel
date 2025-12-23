# Changelog

All notable changes to `ideacrafters/satim-laravel` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
