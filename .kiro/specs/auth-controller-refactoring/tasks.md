# Implementation Tasks

## Phase 1: Foundation (Models & Services)

### Task 1.1: Create VerificationToken Model
**Status:** pending
**Description:** Create Eloquent model for verification_tokens table with relationships and scopes
**Requirements:** Requirement 2
**Files:**
- backend/app/Models/VerificationToken.php

### Task 1.2: Create PasswordResetToken Model
**Status:** pending
**Description:** Create Eloquent model for password_reset_tokens table with scopes
**Requirements:** Requirement 3
**Files:**
- backend/app/Models/PasswordResetToken.php

### Task 1.3: Create TokenService
**Status:** pending
**Description:** Create service for token generation, validation, and cleanup
**Requirements:** Requirement 4
**Files:**
- backend/app/Services/TokenService.php

### Task 1.4: Create MailService
**Status:** pending
**Description:** Create service for email sending operations
**Requirements:** Requirement 5
**Files:**
- backend/app/Services/MailService.php

## Phase 2: Business Logic (AuthService)

### Task 2.1: Create AuthService
**Status:** pending
**Description:** Create service with all authentication business logic methods
**Requirements:** Requirement 6
**Files:**
- backend/app/Services/AuthService.php

## Phase 3: Validation (Form Requests)

### Task 3.1: Create RegisterRequest
**Status:** pending
**Description:** Extract registration validation into Form Request
**Requirements:** Requirement 1
**Files:**
- backend/app/Http/Requests/RegisterRequest.php

### Task 3.2: Create LoginRequest
**Status:** pending
**Description:** Extract login validation into Form Request
**Requirements:** Requirement 1
**Files:**
- backend/app/Http/Requests/LoginRequest.php

### Task 3.3: Create ChangePasswordRequest
**Status:** pending
**Description:** Extract password change validation into Form Request
**Requirements:** Requirement 1
**Files:**
- backend/app/Http/Requests/ChangePasswordRequest.php

### Task 3.4: Create ResetPasswordRequest
**Status:** pending
**Description:** Extract password reset validation into Form Request
**Requirements:** Requirement 1
**Files:**
- backend/app/Http/Requests/ResetPasswordRequest.php

## Phase 4: Controller Refactoring

### Task 4.1: Refactor AuthController - Dependency Injection
**Status:** pending
**Description:** Add constructor with AuthService injection
**Requirements:** Requirement 9
**Files:**
- backend/app/Http/Controllers/AuthController.php

### Task 4.2: Refactor AuthController - Registration Methods
**Status:** pending
**Description:** Refactor register() and registerApi() to use RegisterRequest and AuthService
**Requirements:** Requirement 1, Requirement 6, Requirement 7
**Files:**
- backend/app/Http/Controllers/AuthController.php

### Task 4.3: Refactor AuthController - Login Methods
**Status:** pending
**Description:** Refactor login() and loginApi() to use LoginRequest
**Requirements:** Requirement 1, Requirement 7
**Files:**
- backend/app/Http/Controllers/AuthController.php

### Task 4.4: Refactor AuthController - Email Verification
**Status:** pending
**Description:** Refactor verifyEmailToken() and resendVerificationEmail() to use AuthService
**Requirements:** Requirement 6
**Files:**
- backend/app/Http/Controllers/AuthController.php

### Task 4.5: Refactor AuthController - Password Reset
**Status:** pending
**Description:** Refactor forgotPasswordApi() and resetPasswordApi() to use AuthService and ResetPasswordRequest
**Requirements:** Requirement 1, Requirement 6
**Files:**
- backend/app/Http/Controllers/AuthController.php

### Task 4.6: Refactor AuthController - Password Change
**Status:** pending
**Description:** Refactor changePasswordApi() to use ChangePasswordRequest and AuthService
**Requirements:** Requirement 1, Requirement 6
**Files:**
- backend/app/Http/Controllers/AuthController.php

### Task 4.7: Refactor AuthController - Account Deletion
**Status:** pending
**Description:** Refactor deleteAccountApi() to use AuthService
**Requirements:** Requirement 6
**Files:**
- backend/app/Http/Controllers/AuthController.php

## Phase 5: Testing & Validation

### Task 5.1: Integration Testing
**Status:** pending
**Description:** Test all authentication flows end-to-end
**Requirements:** Requirement 10
**Files:**
- Multiple files

### Task 5.2: Backward Compatibility Verification
**Status:** pending
**Description:** Verify all existing functionality works identically
**Requirements:** Requirement 10
**Files:**
- Multiple files
