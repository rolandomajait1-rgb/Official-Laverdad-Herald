# Requirements Document

## Introduction

This document specifies requirements for refactoring the AuthController in a Laravel application to eliminate code duplication, improve architectural patterns, enhance testability, and follow Laravel best practices. The refactoring addresses critical issues discovered after adding email verification functionality, including massive code duplication across registration methods, direct database calls bypassing Eloquent models, scattered Mail facade usage, and mixed business/controller logic.

## Glossary

- **AuthController**: The Laravel controller responsible for authentication operations including login, registration, logout, password management, and email verification
- **VerificationToken_Model**: An Eloquent model representing email verification tokens stored in the verification_tokens database table
- **PasswordResetToken_Model**: An Eloquent model representing password reset tokens stored in the password_reset_tokens database table
- **AuthService**: A service class that encapsulates authentication business logic including token generation, email sending, and user verification
- **RegisterRequest**: A Form Request class that validates user registration input
- **LoginRequest**: A Form Request class that validates user login input
- **ChangePasswordRequest**: A Form Request class that validates password change input
- **ResetPasswordRequest**: A Form Request class that validates password reset input
- **MailService**: A service class that handles all email sending operations for authentication
- **TokenService**: A service class that handles token generation, validation, and cleanup operations

## Requirements

### Requirement 1: Extract Validation Rules into Form Request Classes

**User Story:** As a developer, I want validation rules extracted into dedicated Form Request classes, so that validation logic is reusable, testable, and follows Laravel conventions.

#### Acceptance Criteria

1. THE AuthController SHALL use RegisterRequest for validating registration input in both register() and registerApi() methods
2. THE AuthController SHALL use LoginRequest for validating login input in both login() and loginApi() methods
3. THE AuthController SHALL use ChangePasswordRequest for validating password change input in changePasswordApi() method
4. THE AuthController SHALL use ResetPasswordRequest for validating password reset input in resetPasswordApi() method
5. THE RegisterRequest SHALL contain validation rules: name (required, string, max 255), email (required, email, unique users, ends_with @student.laverdad.edu.ph), password (required, string, min 8, confirmed, regex lowercase, regex uppercase, regex digit)
6. THE ChangePasswordRequest SHALL contain validation rules: current_password (required, string), password (required, string, min 8, confirmed, regex lowercase, regex uppercase, regex digit)
7. THE ResetPasswordRequest SHALL contain validation rules: email (required, email), token (required, string), password (required, string, min 8, confirmed, regex lowercase, regex uppercase, regex digit)

### Requirement 2: Create VerificationToken Eloquent Model

**User Story:** As a developer, I want a VerificationToken Eloquent model, so that I can use Laravel's ORM features instead of raw database queries.

#### Acceptance Criteria

1. THE VerificationToken_Model SHALL define the table name as verification_tokens
2. THE VerificationToken_Model SHALL define fillable fields: user_id, token, expires_at
3. THE VerificationToken_Model SHALL define a belongsTo relationship with the User model
4. THE VerificationToken_Model SHALL provide a scopeValid() method that filters non-expired tokens
5. WHEN querying verification tokens, THE AuthController SHALL use VerificationToken_Model instead of DB facade

### Requirement 3: Create PasswordResetToken Eloquent Model

**User Story:** As a developer, I want a PasswordResetToken Eloquent model, so that password reset operations use consistent ORM patterns.

#### Acceptance Criteria

1. THE PasswordResetToken_Model SHALL define the table name as password_reset_tokens
2. THE PasswordResetToken_Model SHALL define fillable fields: email, token, created_at
3. THE PasswordResetToken_Model SHALL provide a scopeValid() method that filters tokens created within 24 hours
4. WHEN managing password reset tokens, THE AuthController SHALL use PasswordResetToken_Model instead of DB facade

### Requirement 4: Extract Token Generation Logic into TokenService

**User Story:** As a developer, I want token generation centralized in a TokenService, so that token creation is consistent and testable across all authentication operations.

#### Acceptance Criteria

1. THE TokenService SHALL provide a generateVerificationToken() method that creates a 32-byte hexadecimal token
2. THE TokenService SHALL provide a createVerificationToken() method that accepts a User and returns a VerificationToken_Model instance with 24-hour expiration
3. THE TokenService SHALL provide a deleteUserVerificationTokens() method that removes all verification tokens for a given User
4. THE TokenService SHALL provide a validateVerificationToken() method that accepts a token string and returns the associated VerificationToken_Model if valid or null if invalid/expired
5. WHEN creating verification tokens in register(), registerApi(), or resendVerificationEmail(), THE AuthController SHALL use TokenService methods

### Requirement 5: Extract Email Sending Logic into MailService

**User Story:** As a developer, I want email sending centralized in a MailService, so that email operations are testable, mockable, and follow dependency injection patterns.

#### Acceptance Criteria

1. THE MailService SHALL provide a sendVerificationEmail() method that accepts a User and verification token string
2. THE MailService SHALL provide a sendPasswordResetEmail() method that accepts a User and reset token string
3. WHEN sending verification emails, THE MailService SHALL use the emails.verify-email view template
4. WHEN sending password reset emails, THE MailService SHALL use the emails.reset-password view template
5. IF email sending fails, THEN THE MailService SHALL log the error and throw a MailException
6. WHEN sending emails in AuthController, THE AuthController SHALL use MailService methods instead of Mail facade

### Requirement 6: Extract Business Logic into AuthService

**User Story:** As a developer, I want authentication business logic extracted into an AuthService, so that controllers remain thin and business logic is reusable and testable.

#### Acceptance Criteria

1. THE AuthService SHALL provide a registerUser() method that accepts validated registration data and returns a User instance
2. THE AuthService SHALL provide a createUserWithVerification() method that creates a User, generates a verification token, and sends verification email
3. THE AuthService SHALL provide a verifyUserEmail() method that accepts a token string and marks the user as verified if valid
4. THE AuthService SHALL provide a resendVerification() method that accepts an email address and sends a new verification email if user exists and is unverified
5. THE AuthService SHALL provide a initiatePasswordReset() method that accepts an email address, creates a reset token, and sends reset email
6. THE AuthService SHALL provide a resetPassword() method that accepts email, token, and new password, validates the token, and updates the password
7. WHEN performing registration in register() or registerApi(), THE AuthController SHALL delegate to AuthService.createUserWithVerification()
8. WHEN verifying email in verifyEmailToken(), THE AuthController SHALL delegate to AuthService.verifyUserEmail()
9. WHEN resending verification in resendVerificationEmail(), THE AuthController SHALL delegate to AuthService.resendVerification()
10. WHEN initiating password reset in forgotPasswordApi(), THE AuthController SHALL delegate to AuthService.initiatePasswordReset()
11. WHEN resetting password in resetPasswordApi(), THE AuthController SHALL delegate to AuthService.resetPassword()

### Requirement 7: Eliminate Code Duplication Between Web and API Methods

**User Story:** As a developer, I want registration and login logic unified between web and API endpoints, so that business logic is not duplicated and maintenance is simplified.

#### Acceptance Criteria

1. THE register() method SHALL use the same validation, user creation, and email verification logic as registerApi() through shared Form Requests and Services
2. THE login() method SHALL use the same validation and authentication logic as loginApi() through shared Form Requests
3. WHEN registration succeeds, THE register() method SHALL return a redirect response and THE registerApi() method SHALL return a JSON response, but both SHALL use identical business logic
4. WHEN login succeeds, THE login() method SHALL create a session and THE loginApi() method SHALL create a token, but both SHALL use identical authentication logic

### Requirement 8: Implement Consistent Error Handling

**User Story:** As a developer, I want consistent error handling across all authentication operations, so that errors are properly logged, reported, and handled uniformly.

#### Acceptance Criteria

1. WHEN email sending fails in any authentication operation, THEN THE MailService SHALL log the error with context (user email, operation type)
2. WHEN token validation fails in verifyEmailToken(), THEN THE AuthService SHALL return a structured error response indicating the failure reason
3. WHEN password reset token is expired in resetPasswordApi(), THEN THE AuthService SHALL delete the expired token before returning an error
4. WHEN database operations fail in any service method, THEN THE service SHALL log the error and throw a descriptive exception
5. THE AuthController SHALL catch service exceptions and return appropriate HTTP responses (400 for validation errors, 404 for not found, 500 for server errors)

### Requirement 9: Improve Testability Through Dependency Injection

**User Story:** As a developer, I want services injected into the AuthController constructor, so that I can mock dependencies in unit tests.

#### Acceptance Criteria

1. THE AuthController SHALL accept AuthService, TokenService, and MailService as constructor parameters
2. THE AuthController constructor SHALL use Laravel's service container for automatic dependency injection
3. WHEN testing AuthController, developers SHALL be able to mock AuthService, TokenService, and MailService
4. THE AuthService SHALL accept TokenService and MailService as constructor parameters
5. WHEN testing AuthService, developers SHALL be able to mock TokenService and MailService

### Requirement 10: Maintain Backward Compatibility

**User Story:** As a user of the application, I want all existing authentication functionality to work identically after refactoring, so that my experience is uninterrupted.

#### Acceptance Criteria

1. THE refactored AuthController SHALL maintain all existing route endpoints with identical URLs
2. THE refactored AuthController SHALL accept and return identical request/response formats for all API methods
3. THE refactored AuthController SHALL maintain identical redirect behavior for all web methods
4. THE refactored AuthController SHALL maintain identical validation error messages
5. THE refactored AuthController SHALL maintain identical email templates and content
6. THE refactored AuthController SHALL maintain identical token expiration times (24 hours for verification, 24 hours for password reset)
7. THE refactored AuthController SHALL maintain identical rate limiting behavior in verifyEmailToken()
8. THE refactored AuthController SHALL maintain identical security measures (timing attack prevention in loginApi(), password hashing, token cleanup)
