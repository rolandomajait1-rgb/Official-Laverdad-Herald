# Design Document

## High-Level Design

### Architecture Overview

The refactored authentication system follows a layered architecture pattern:

```
┌─────────────────────────────────────────────────────────────┐
│                     HTTP Layer                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         AuthController (Thin Controller)             │   │
│  │  - Handles HTTP requests/responses                   │   │
│  │  - Delegates to services                             │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Validation Layer                           │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Form Requests (RegisterRequest, LoginRequest, etc.) │   │
│  │  - Validates incoming data                           │   │
│  │  - Returns validation errors                         │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Service Layer                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ AuthService  │  │ TokenService │  │ MailService  │      │
│  │              │  │              │  │              │      │
│  │ - User reg   │  │ - Token gen  │  │ - Email send │      │
│  │ - Email ver  │  │ - Token val  │  │ - Templates  │      │
│  │ - Password   │  │ - Cleanup    │  │ - Error hand │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Data Layer                                │
│  ┌──────────────┐  ┌──────────────────────┐  ┌──────────┐  │
│  │ User Model   │  │ VerificationToken    │  │ Password │  │
│  │              │  │ Model                │  │ Reset    │  │
│  │              │  │                      │  │ Token    │  │
│  └──────────────┘  └──────────────────────┘  └──────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

#### Controllers
- **AuthController**: Thin HTTP layer that handles requests, delegates to services, and formats responses

#### Form Requests
- **RegisterRequest**: Validates user registration data
- **LoginRequest**: Validates login credentials
- **ChangePasswordRequest**: Validates password change data
- **ResetPasswordRequest**: Validates password reset data

#### Services
- **AuthService**: Orchestrates authentication workflows (registration, verification, password reset)
- **TokenService**: Manages token generation, validation, and cleanup
- **MailService**: Handles all email sending operations

#### Models
- **User**: Existing user model
- **VerificationToken**: Email verification tokens
- **PasswordResetToken**: Password reset tokens

### Data Flow Examples

#### Registration Flow
```
User Request → AuthController::registerApi()
    → RegisterRequest (validation)
    → AuthService::createUserWithVerification()
        → User::create()
        → TokenService::createVerificationToken()
        → MailService::sendVerificationEmail()
    → JSON Response
```

#### Email Verification Flow
```
User Request → AuthController::verifyEmailToken()
    → AuthService::verifyUserEmail()
        → TokenService::validateVerificationToken()
        → User::markEmailAsVerified()
        → TokenService::deleteUserVerificationTokens()
    → Redirect Response
```

## Low-Level Design

### Models

#### VerificationToken Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationToken extends Model
{
    protected $table = 'verification_tokens';
    
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    
    // Relationships
    public function user(): BelongsTo;
    
    // Scopes
    public function scopeValid($query);
    
    // Methods
    public function isExpired(): bool;
}
```

#### PasswordResetToken Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';
    
    public $timestamps = false;
    
    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
    ];
    
    // Scopes
    public function scopeValid($query);
    
    // Methods
    public function isExpired(): bool;
}
```

### Services

#### TokenService
```php
namespace App\Services;

use App\Models\User;
use App\Models\VerificationToken;
use App\Models\PasswordResetToken;

class TokenService
{
    /**
     * Generate a random 32-byte hexadecimal token
     */
    public function generateToken(): string;
    
    /**
     * Create a verification token for a user
     * 
     * @param User $user
     * @return VerificationToken
     */
    public function createVerificationToken(User $user): VerificationToken;
    
    /**
     * Validate a verification token and return the token model if valid
     * 
     * @param string $token
     * @return VerificationToken|null
     */
    public function validateVerificationToken(string $token): ?VerificationToken;
    
    /**
     * Delete all verification tokens for a user
     * 
     * @param User $user
     * @return void
     */
    public function deleteUserVerificationTokens(User $user): void;
    
    /**
     * Create a password reset token for an email
     * 
     * @param string $email
     * @return array ['token' => string, 'hashedToken' => string]
     */
    public function createPasswordResetToken(string $email): array;
    
    /**
     * Validate a password reset token
     * 
     * @param string $email
     * @param string $token
     * @return PasswordResetToken|null
     */
    public function validatePasswordResetToken(string $email, string $token): ?PasswordResetToken;
    
    /**
     * Delete password reset token for an email
     * 
     * @param string $email
     * @return void
     */
    public function deletePasswordResetToken(string $email): void;
}
```

#### MailService
```php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Send email verification email
     * 
     * @param User $user
     * @param string $token
     * @return void
     * @throws \Exception
     */
    public function sendVerificationEmail(User $user, string $token): void;
    
    /**
     * Send password reset email
     * 
     * @param User $user
     * @param string $token
     * @return void
     * @throws \Exception
     */
    public function sendPasswordResetEmail(User $user, string $token): void;
    
    /**
     * Build verification URL
     * 
     * @param string $token
     * @return string
     */
    private function buildVerificationUrl(string $token): string;
    
    /**
     * Build password reset URL
     * 
     * @param string $token
     * @param string $email
     * @return string
     */
    private function buildPasswordResetUrl(string $token, string $email): string;
}
```

#### AuthService
```php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private TokenService $tokenService;
    private MailService $mailService;
    
    public function __construct(TokenService $tokenService, MailService $mailService);
    
    /**
     * Register a new user with email verification
     * 
     * @param array $data ['name', 'email', 'password']
     * @return User
     * @throws \Exception
     */
    public function createUserWithVerification(array $data): User;
    
    /**
     * Verify user email using token
     * 
     * @param string $token
     * @return array ['success' => bool, 'message' => string, 'user' => User|null]
     */
    public function verifyUserEmail(string $token): array;
    
    /**
     * Resend verification email
     * 
     * @param string $email
     * @return array ['success' => bool, 'message' => string]
     */
    public function resendVerification(string $email): array;
    
    /**
     * Initiate password reset process
     * 
     * @param string $email
     * @return array ['success' => bool, 'message' => string]
     */
    public function initiatePasswordReset(string $email): array;
    
    /**
     * Reset user password
     * 
     * @param string $email
     * @param string $token
     * @param string $newPassword
     * @return array ['success' => bool, 'message' => string]
     */
    public function resetPassword(string $email, string $token, string $newPassword): array;
    
    /**
     * Change user password
     * 
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return array ['success' => bool, 'message' => string]
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): array;
    
    /**
     * Delete user account
     * 
     * @param User $user
     * @param string $password
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteAccount(User $user, string $password): array;
}
```

### Form Requests

#### RegisterRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool;
    
    public function rules(): array;
    
    public function messages(): array;
}
```

#### LoginRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool;
    
    public function rules(): array;
    
    public function messages(): array;
}
```

#### ChangePasswordRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool;
    
    public function rules(): array;
    
    public function messages(): array;
}
```

#### ResetPasswordRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool;
    
    public function rules(): array;
    
    public function messages(): array;
}
```

### Refactored AuthController

```php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private AuthService $authService;
    
    public function __construct(AuthService $authService);
    
    // Web Routes
    public function showLoginForm();
    public function login(LoginRequest $request);
    public function showRegistrationForm();
    public function register(RegisterRequest $request);
    public function logout(Request $request);
    public function verifyEmailToken(Request $request);
    
    // API Routes
    public function loginApi(LoginRequest $request);
    public function registerApi(RegisterRequest $request);
    public function logoutApi(Request $request);
    public function changePasswordApi(ChangePasswordRequest $request);
    public function deleteAccountApi(Request $request);
    public function verifyEmail(Request $request);
    public function resendVerificationEmail(Request $request);
    public function forgotPasswordApi(Request $request);
    public function resetPasswordApi(ResetPasswordRequest $request);
}
```

## Implementation Strategy

### Phase 1: Foundation (Models & Services)
1. Create VerificationToken model
2. Create PasswordResetToken model
3. Create TokenService
4. Create MailService
5. Write unit tests for TokenService and MailService

### Phase 2: Business Logic (AuthService)
1. Create AuthService with dependency injection
2. Implement createUserWithVerification()
3. Implement verifyUserEmail()
4. Implement resendVerification()
5. Implement initiatePasswordReset()
6. Implement resetPassword()
7. Implement changePassword()
8. Implement deleteAccount()
9. Write unit tests for AuthService

### Phase 3: Validation (Form Requests)
1. Create RegisterRequest
2. Create LoginRequest
3. Create ChangePasswordRequest
4. Create ResetPasswordRequest
5. Write validation tests

### Phase 4: Controller Refactoring
1. Inject AuthService into AuthController
2. Refactor register() and registerApi() to use AuthService
3. Refactor verifyEmailToken() to use AuthService
4. Refactor resendVerificationEmail() to use AuthService
5. Refactor forgotPasswordApi() to use AuthService
6. Refactor resetPasswordApi() to use AuthService
7. Refactor changePasswordApi() to use AuthService
8. Refactor deleteAccountApi() to use AuthService
9. Update all methods to use Form Requests
10. Write integration tests

### Phase 5: Testing & Validation
1. Run full test suite
2. Manual testing of all authentication flows
3. Verify backward compatibility
4. Performance testing
5. Security audit

## Design Decisions

### Why Service Layer?
- Separates business logic from HTTP concerns
- Makes code testable without HTTP layer
- Enables reuse across different controllers/commands
- Follows Single Responsibility Principle

### Why Form Requests?
- Laravel convention for validation
- Keeps controllers thin
- Reusable validation rules
- Automatic validation before controller methods

### Why Eloquent Models for Tokens?
- Consistent with Laravel patterns
- Enables relationships and scopes
- Better than raw DB queries
- Type safety and IDE support

### Why Dependency Injection?
- Testability (can mock dependencies)
- Flexibility (can swap implementations)
- Laravel service container handles it automatically
- Follows SOLID principles

## Security Considerations

1. **Token Generation**: Use cryptographically secure random_bytes()
2. **Password Hashing**: Continue using Hash::make() with bcrypt
3. **Timing Attack Prevention**: Maintain dummy hash check in loginApi()
4. **Token Cleanup**: Delete tokens after use or expiration
5. **Email Verification**: Maintain 24-hour expiration
6. **Password Reset**: Maintain 24-hour expiration and token invalidation after use
7. **Rate Limiting**: Maintain existing rate limiting on sensitive endpoints

## Backward Compatibility

All existing functionality will be preserved:
- Same route endpoints
- Same request/response formats
- Same validation messages
- Same email templates
- Same redirect behavior
- Same security measures
- Same token expiration times

The refactoring is purely internal - external API contracts remain unchanged.
