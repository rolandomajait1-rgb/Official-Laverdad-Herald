# Image Storage Persistence Fix - Bugfix Design

## Overview

This bugfix addresses the critical issue where article images are lost after server restarts on the Render platform due to ephemeral filesystem storage. The fix will migrate the image upload implementation from local filesystem storage (`storage/app/public/articles/`) to Cloudinary cloud storage, ensuring images persist across deployments. The solution includes updating the ArticleController's `store()` and `update()` methods to use Cloudinary, modifying the Article model's `getFeaturedImageUrlAttribute()` to handle both cloud URLs and legacy local paths, and providing a migration command to upload any remaining local images to Cloudinary.

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug - when images are uploaded and stored to the local ephemeral filesystem instead of persistent cloud storage
- **Property (P)**: The desired behavior - images should be uploaded to Cloudinary and their secure URLs stored in the database, persisting across server restarts
- **Preservation**: Existing behaviors that must remain unchanged - placeholder images for articles without images, validation rules, eager loading, and local development support
- **ArticleController**: The controller in `backend/app/Http/Controllers/ArticleController.php` that handles article CRUD operations including image uploads
- **getFeaturedImageUrlAttribute()**: The accessor method in `backend/app/Models/Article.php` that generates the full URL for article featured images
- **Ephemeral Filesystem**: Temporary storage on Render that is wiped on every deployment or restart, causing data loss
- **Cloudinary**: Cloud-based image and video management service that provides persistent storage, CDN delivery, and image transformations
- **Migration Command**: An Artisan command to upload existing local images to Cloudinary and update database references

## Bug Details

### Fault Condition

The bug manifests when an article image is uploaded via `ArticleController::store()` or `ArticleController::update()`. The methods use `$request->file('featured_image')->store('articles', 'public')` which stores images to the local ephemeral filesystem at `storage/app/public/articles/`. When the Render server restarts or redeploys, all files in this directory are permanently lost, resulting in broken image URLs that return 404 errors.

**Formal Specification:**

```
FUNCTION isBugCondition(input)
  INPUT: input of type ImageUploadRequest
  OUTPUT: boolean
  
  RETURN input.hasFile('featured_image') == true
         AND input.file('featured_image').store() uses local 'public' disk
         AND server.platform == 'Render' (ephemeral filesystem)
         AND NOT uploadedToCloudinary(input.file('featured_image'))
END FUNCTION
```

### Examples

- **Example 1**: Admin uploads article with featured image via POST `/api/articles` → Image stored as `articles/abc123.jpg` in local filesystem → Database stores `articles/abc123.jpg` → Server restarts → Image file deleted → `getFeaturedImageUrlAttribute()` returns `https://official-laverdad-herald.onrender.com/storage/articles/abc123.jpg` → URL returns 404

- **Example 2**: Admin updates existing article with new featured image via PUT `/api/articles/{id}` → New image stored locally as `articles/xyz789.jpg` → Database updated with `articles/xyz789.jpg` → Deployment occurs → Image lost → Broken image displayed on frontend

- **Example 3**: Article created with image before fix → Local path `articles/old-image.jpg` in database → Server restart → Image lost → `getFeaturedImageUrlAttribute()` generates broken URL

- **Edge Case**: Article created in local development environment with local image → Deployed to production → Image not available in production filesystem → Broken image (expected behavior for local dev, but migration command should handle this)

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Articles created without images must continue to return the placeholder image URL (`https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald`)
- Articles with existing full HTTP/HTTPS URLs (already using cloud storage) must continue to return those URLs without modification
- Image validation rules must remain the same: `nullable|image|mimes:jpeg,png,jpg|max:5120`
- The `featured_image_url` attribute must continue to be appended to article responses via the `$appends` property
- Local development environment must continue to support local filesystem storage for development purposes (environment-based behavior)

**Scope:**
All inputs that do NOT involve uploading new article images should be completely unaffected by this fix. This includes:
- Article creation without images
- Article updates without image changes
- Article queries and retrieval operations
- Article deletion operations
- Other article operations (like, share, etc.)

## Hypothesized Root Cause

Based on the bug description and code analysis, the root causes are:

1. **Hardcoded Local Storage**: Both `store()` and `update()` methods use `->store('articles', 'public')` which explicitly targets the local 'public' disk instead of a cloud storage disk
   - Line 121 in `store()`: `$imagePath = $request->file('featured_image')->store('articles', 'public');`
   - Line 270 in `update()`: `$data['featured_image'] = $request->file('featured_image')->store('articles', 'public');`

2. **No Cloudinary Integration**: Although Cloudinary is configured in the environment, the ArticleController does not use the Cloudinary SDK to upload images

3. **Path-Based Storage**: The database stores relative paths (`articles/filename.jpg`) instead of full cloud URLs, which works for local storage but breaks when files are lost

4. **No Migration Path**: Existing articles with local image paths have no automated way to migrate to cloud storage, leaving legacy data broken after the fix is deployed

## Correctness Properties

Property 1: Fault Condition - Images Uploaded to Cloudinary

_For any_ image upload request where `featured_image` file is present in the request, the fixed ArticleController SHALL upload the image to Cloudinary using the Cloudinary SDK, store the returned secure HTTPS URL (e.g., `https://res.cloudinary.com/...`) in the `featured_image` database column, and ensure the image persists across server restarts and redeployments.

**Validates: Requirements 2.1, 2.2, 2.3**

Property 2: Preservation - Non-Image Upload Behavior

_For any_ article operation that does NOT involve uploading a new featured image (article creation without image, article updates without image changes, article queries, deletions), the fixed code SHALL produce exactly the same behavior as the original code, preserving placeholder image logic, URL generation for existing cloud URLs, validation rules, and local development support.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File 1**: `backend/app/Http/Controllers/ArticleController.php`

**Changes**:
1. **Add Cloudinary Import**: Add `use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;` at the top of the file

2. **Update store() Method (Line 121)**: Replace local storage with Cloudinary upload
   - Replace: `$imagePath = $request->file('featured_image')->store('articles', 'public');`
   - With: Cloudinary upload that returns secure URL and stores full URL in database
   - Add error handling for Cloudinary upload failures
   - Maintain transaction integrity

3. **Update update() Method (Line 270)**: Replace local storage with Cloudinary upload
   - Replace: `$data['featured_image'] = $request->file('featured_image')->store('articles', 'public');`
   - With: Cloudinary upload that returns secure URL
   - Add error handling for Cloudinary upload failures
   - Consider cleanup of old Cloudinary images if replacing existing cloud images

**File 2**: `backend/app/Models/Article.php`

**Function**: `getFeaturedImageUrlAttribute()`

**Changes**:
1. **No Changes Required**: The current implementation already handles full HTTP/HTTPS URLs correctly by returning them as-is (Line 60-62)
   - The method checks `if (str_starts_with($path, 'http'))` and returns the URL directly
   - This will work seamlessly with Cloudinary URLs

2. **Optional Enhancement**: Add logging or fallback for broken local paths in production to help identify unmigrated images

**File 3**: `backend/app/Console/Commands/MigrateImagesToCloudinary.php` (New File)

**Purpose**: Create Artisan command to migrate existing local images to Cloudinary

**Implementation**:
1. Query all articles with non-null `featured_image` that don't start with 'http'
2. For each article, check if local file exists in `storage/app/public/`
3. Upload existing file to Cloudinary with original filename
4. Update article's `featured_image` column with Cloudinary secure URL
5. Log success/failure for each migration
6. Provide summary statistics

**File 4**: `backend/.env` (Configuration)

**Changes**: Ensure Cloudinary credentials are configured
- `CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME`
- Verify configuration is present in production environment

**File 5**: `backend/composer.json` (Dependency)

**Changes**: Ensure Cloudinary package is installed
- Verify `cloudinary-labs/cloudinary-laravel` is in dependencies
- Run `composer install` if not present

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bug on unfixed code by simulating server restarts and verifying image loss, then verify the fix works correctly by confirming Cloudinary uploads and preservation of existing behaviors.

### Exploratory Fault Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm that local storage causes image loss after simulated server restarts.

**Test Plan**: Create test articles with images using the UNFIXED code, verify images are stored locally, simulate server restart by clearing the storage directory, then verify images are broken. This confirms the root cause.

**Test Cases**:
1. **Store Method Image Loss Test**: Create article with image via `store()` → Verify image stored in `storage/app/public/articles/` → Clear storage directory → Verify `getFeaturedImageUrlAttribute()` returns broken URL (will fail on unfixed code - image lost)

2. **Update Method Image Loss Test**: Update article with new image via `update()` → Verify image stored locally → Clear storage directory → Verify image URL broken (will fail on unfixed code - image lost)

3. **Database Path Inspection Test**: Create article with image → Inspect database `featured_image` column → Verify it contains relative path like `articles/abc123.jpg` instead of full URL (will fail on unfixed code - not using cloud storage)

4. **Production Simulation Test**: Deploy unfixed code to staging environment → Upload images → Trigger redeployment → Verify images are lost (will fail on unfixed code - ephemeral filesystem)

**Expected Counterexamples**:
- Images stored locally are deleted when storage directory is cleared
- Database contains relative paths that become invalid after file loss
- `getFeaturedImageUrlAttribute()` generates URLs pointing to non-existent files
- Possible causes: hardcoded local storage, no cloud integration, path-based storage

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds (image uploads), the fixed function uploads to Cloudinary and stores persistent URLs.

**Pseudocode:**
```
FOR ALL imageUploadRequest WHERE hasFile('featured_image') DO
  result := ArticleController_fixed.store(imageUploadRequest)
  ASSERT result.featured_image starts_with 'https://res.cloudinary.com/'
  ASSERT cloudinaryImageExists(result.featured_image)
  ASSERT imageAccessibleAfterRestart(result.featured_image)
END FOR
```

**Test Cases**:
1. **Cloudinary Upload Verification**: Create article with image using fixed code → Verify `featured_image` column contains full Cloudinary URL → Verify URL is accessible
2. **Persistence After Restart**: Upload image → Clear local storage → Verify image still accessible via Cloudinary URL
3. **Update Method Cloudinary Upload**: Update article with new image → Verify new Cloudinary URL stored → Verify old image handling
4. **Error Handling**: Simulate Cloudinary upload failure → Verify graceful error handling and transaction rollback

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold (non-image operations), the fixed function produces the same result as the original function.

**Pseudocode:**
```
FOR ALL articleOperation WHERE NOT isImageUpload(articleOperation) DO
  ASSERT ArticleController_original(articleOperation) = ArticleController_fixed(articleOperation)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Test all non-image operations on both UNFIXED and FIXED code, verify identical behavior.

**Test Cases**:
1. **Placeholder Image Preservation**: Create article without image on both versions → Verify both return placeholder URL → Verify placeholder URL is identical
2. **Existing Cloud URL Preservation**: Create article with existing HTTP URL on both versions → Verify both return URL unchanged → Verify no upload occurs
3. **Validation Preservation**: Submit invalid image (wrong type, too large) on both versions → Verify both reject with same validation errors
4. **Local Development Preservation**: Run in local environment → Verify local storage still works for development → Verify environment-based behavior maintained
5. **Query Operations Preservation**: Query articles on both versions → Verify `featured_image_url` appended identically → Verify eager loading works the same

### Unit Tests

- Test Cloudinary upload in `store()` method with valid image file
- Test Cloudinary upload in `update()` method with valid image file
- Test error handling when Cloudinary upload fails
- Test transaction rollback on upload failure
- Test `getFeaturedImageUrlAttribute()` with Cloudinary URL (returns as-is)
- Test `getFeaturedImageUrlAttribute()` with local path (generates storage URL)
- Test `getFeaturedImageUrlAttribute()` with null image (returns placeholder)
- Test migration command with existing local images
- Test migration command with missing local files
- Test migration command with already-migrated images (idempotency)

### Property-Based Tests

- Generate random valid image files and verify all are uploaded to Cloudinary successfully
- Generate random article data without images and verify placeholder logic preserved across many scenarios
- Generate random existing cloud URLs and verify they are never re-uploaded or modified
- Generate random invalid image inputs and verify validation errors are consistent
- Test migration command with various database states (mixed local/cloud images, missing files, etc.)

### Integration Tests

- Test full article creation flow with image upload to Cloudinary
- Test full article update flow with image replacement on Cloudinary
- Test article retrieval with Cloudinary URLs in various contexts (public API, admin API, eager loading)
- Test migration command on production-like dataset with mixed image states
- Test deployment simulation: upload images, redeploy application, verify images still accessible
- Test local development workflow: verify local storage still works in development environment
