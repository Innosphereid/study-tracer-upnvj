.
|-- README.md
|-- app
| |-- Console
| | |-- Command
| | | `-- CreateAdminUser.php
|   |   `-- Commands
| | |-- GenerateTestReport.php
| | `-- ServeWithCacheClear.php
|   |-- Contracts
|   |   |-- Repositories
|   |   |   |-- PasswordResetRepositoryInterface.php
|   |   |   |-- RepositoryInterface.php
|   |   |   `-- UserRepositoryInterface.php
| | `-- Services
|   |       |-- ActivityLoggerInterface.php
|   |       |-- AuthenticationServiceInterface.php
|   |       |-- AuthenticatorInterface.php
|   |       `-- PasswordResetServiceInterface.php
| |-- Helpers
| | `-- SessionManager.php
|   |-- Http
|   |   |-- Controllers
|   |   |   |-- Admin
|   |   |   |   `-- DashboardController.php
| | | |-- Auth
| | | | |-- AuthController.php
| | | | `-- ResetPasswordController.php
|   |   |   |-- Controller.php
|   |   |   |-- SecurityController.php
|   |   |   `-- SuperAdmin
| | | `-- DashboardController.php
|   |   |-- Kernel.php
|   |   |-- Middleware
|   |   |   |-- CheckUserRole.php
|   |   |   |-- EnsureUserHasRole.php
|   |   |   `-- RedirectIfAuthenticated.php
| | `-- Requests
|   |       `-- Auth
| | |-- LoginRequest.php
| | |-- ResetPasswordRequest.php
| | |-- SendResetLinkRequest.php
| | `-- VerifyOtpRequest.php
|   |-- Mail
|   |   |-- PasswordResetMail.php
|   |   `-- PasswordResetSuccessMail.php
| |-- Models
| | |-- ActivityLog.php
| | |-- PasswordResetToken.php
| | |-- Session.php
| | `-- User.php
|   |-- Providers
|   |   |-- AppServiceProvider.php
|   |   |-- AuthServiceProvider.php
|   |   |-- AuthServicesProvider.php
|   |   |-- LoggerServiceProvider.php
|   |   |-- PasswordResetServiceProvider.php
|   |   |-- RateLimitingServiceProvider.php
|   |   `-- RepositoryServiceProvider.php
| |-- Repositories
| | |-- BaseRepository.php
| | |-- PasswordResetRepository.php
| | `-- UserRepository.php
|   `-- Services
| |-- ActivityLoggerService.php
| |-- AuthenticationService.php
| |-- AuthenticatorService.php
| `-- PasswordResetService.php
|-- artisan
|-- bootstrap
|   |-- app.php
|   |-- cache
|   |   |-- events.php
|   |   |-- packages.php
|   |   `-- services.php
| `-- providers.php
|-- composer.json
|-- composer.lock
|-- config
|   |-- app.php
|   |-- auth.php
|   |-- cache.php
|   |-- database.php
|   |-- filesystems.php
|   |-- logging.php
|   |-- mail.php
|   |-- queue.php
|   |-- services.php
|   `-- session.php
|-- database
| |-- database.sqlite
| |-- factories
| | `-- UserFactory.php
|   |-- migrations
|   |   |-- 0001_01_01_000001_create_cache_table.php
|   |   |-- 0001_01_01_000002_create_jobs_table.php
|   |   |-- 2025_03_22_231631_create_users_table.php
|   |   |-- 2025_03_22_233646_create_activity_logs_table.php
|   |   |-- 2025_03_22_235002_create_sessions_table.php
|   |   `-- 2025_03_23_141810_create_password_reset_tokens_table.php
| `-- seeders
|       |-- DatabaseSeeder.php
|       `-- UserSeeder.php
|-- lang
| |-- en
| | `-- auth.php
|   `-- id
| `-- auth.php
|-- package-lock.json
|-- package.json
|-- phpunit.xml
|-- public
|   |-- build
|   |   |-- assets
|   |   |   |-- app-BTnrbYJB.js
|   |   |   `-- app-Coxap04O.css
| | `-- manifest.json
|   |-- favicon.ico
|   |-- index.php
|   |-- logo-upnvj.png
|   `-- robots.txt
|-- resources
| |-- css
| | `-- app.css
|   |-- js
|   |   |-- app.js
|   |   |-- bootstrap.js
|   |   `-- email-check.js
| `-- views
|       |-- admin
|       |   `-- dashboard.blade.php
| |-- auth
| | `-- login.blade.php
|       |-- components
|       |   |-- alert.blade.php
|       |   |-- auth
|       |   |   |-- login-footer.blade.php
|       |   |   |-- login-form.blade.php
|       |   |   |-- login-header.blade.php
|       |   |   |-- login-illustration.blade.php
|       |   |   |-- password-strength-meter.blade.php
|       |   |   |-- reset-password
|       |   |   |   |-- create-password-form.blade.php
|       |   |   |   |-- header.blade.php
|       |   |   |   |-- otp-verification-form.blade.php
|       |   |   |   |-- request-form.blade.php
|       |   |   |   `-- success-message.blade.php
| | | `-- session-status.blade.php
|       |   |-- dashboard
|       |   |   |-- activity-log.blade.php
|       |   |   |-- chart-card.blade.php
|       |   |   |-- quick-link.blade.php
|       |   |   |-- sidebar-dropdown.blade.php
|       |   |   |-- sidebar-item.blade.php
|       |   |   |-- sidebar.blade.php
|       |   |   `-- stat-card.blade.php
| | |-- flash-messages.blade.php
| | `-- form
|       |       |-- button.blade.php
|       |       |-- checkbox.blade.php
|       |       |-- input-error.blade.php
|       |       |-- input-label.blade.php
|       |       |-- otp-input.blade.php
|       |       |-- password-input.blade.php
|       |       `-- text-input.blade.php
| |-- dashboard
| | `-- index.blade.php
|       |-- emails
|       |   `-- password
| | |-- reset-request.blade.php
| | `-- reset-success.blade.php
|       |-- layouts
|       |   |-- app.blade.php
|       |   |-- auth.blade.php
|       |   `-- dashboard.blade.php
| |-- password
| | |-- request.blade.php
| | |-- reset.blade.php
| | |-- success.blade.php
| | `-- verify-otp.blade.php
|       |-- superadmin
|       |   `-- dashboard.blade.php
| `-- welcome.blade.php
|-- routes
|   |-- console.php
|   `-- web.php
|-- storage
| |-- app
| | |-- private
| | `-- public
|   |-- framework
|   |   |-- cache
|   |   |   `-- data
| | |-- sessions
| | |-- testing
| |-- logs
| | `-- laravel.log
|   `-- test-reports
| |-- test_report_2025_03_23_004301.txt
| |-- test_report_2025_03_23_004507.txt
| |-- test_report_2025_03_23_004910.txt
| |-- test_report_2025_03_23_005128.txt
| |-- test_report_2025_03_23_005633.txt
| `-- test_report_2025_03_23_005812.txt
|-- study-tracer-upnvj.md
|-- tests
|   |-- Feature
|   |   `-- Auth
| | |-- AuthenticationIntegrationTest.php
| | |-- LoginSecurityTest.php
| | |-- LoginTest.php
| | |-- LogoutTest.php
| | `-- RoleAuthorizationTest.php
|   |-- TestCase.php
|   `-- Unit
| |-- Middleware
| | `-- CheckUserRoleTest.php
|       |-- Models
|       |   `-- UserTest.php
| |-- Repositories
| | `-- UserRepositoryTest.php
|       `-- Services
| |-- ActivityLoggerServiceTest.php
| |-- AuthenticationServiceTest.php
| `-- AuthenticatorServiceTest.php
`-- vite.config.js

72 directories, 172 files
