# Bugfix Requirements Document

## Introduction

The Laravel application experiences broken images in production because the image upload implementation is still using the local ephemeral filesystem (`storage/app/public/articles/`) instead of the configured Cloudinary cloud storage. When the server restarts or redeploys on the Render platform, all locally stored images are permanently lost, resulting in broken image URLs and a degraded user experience.

Although Cloudinary is configured and available, the ArticleController's `store()` and `update()` methods are not using it. The fix will update the image upload logic to consistently use Cloudinary for all new uploads and provide a migration path for any remaining local images.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN an article image is uploaded via `ArticleController::store()` or `ArticleController::update()` THEN the image is stored to the local filesystem using `store('articles', 'public')` instead of uploading to Cloudinary

1.2 WHEN an article image is stored locally on the ephemeral filesystem THEN the image is lost after server restart/redeployment on the Render platform

1.3 WHEN the server restarts or redeploys THEN all locally stored images become inaccessible and their URLs return 404 errors

1.4 WHEN an article with a local filesystem path (e.g., `articles/filename.jpg`) is accessed after server restart THEN the `getFeaturedImageUrlAttribute()` generates a URL pointing to a non-existent file on the ephemeral filesystem

### Expected Behavior (Correct)

2.1 WHEN an article image is uploaded via `ArticleController::store()` or `ArticleController::update()` THEN the system SHALL upload the image to Cloudinary and store the full Cloudinary URL (e.g., `https://res.cloudinary.com/...`) in the database

2.2 WHEN an image is successfully uploaded to Cloudinary THEN the system SHALL store the secure HTTPS URL returned by Cloudinary in the `featured_image` column

2.3 WHEN the server restarts or redeploys THEN the system SHALL continue to serve all Cloudinary-hosted images without any loss or broken links

2.4 WHEN an article with a Cloudinary URL is accessed THEN the `getFeaturedImageUrlAttribute()` SHALL return the cloud URL directly without modification

2.5 WHEN any remaining local images need to be migrated THEN the system SHALL provide an automated command to upload them to Cloudinary and update database references

### Unchanged Behavior (Regression Prevention)

3.1 WHEN an article is created without an image THEN the system SHALL CONTINUE TO return the placeholder image URL

3.2 WHEN an article already has a full HTTP/HTTPS URL (cloud storage) THEN the system SHALL CONTINUE TO return that URL without modification

3.3 WHEN article validation occurs THEN the system SHALL CONTINUE TO enforce the same image file type (jpeg, png, jpg) and size (max 5120KB) constraints

3.4 WHEN articles are queried with eager loading THEN the system SHALL CONTINUE TO append the `featured_image_url` attribute to the response

3.5 WHEN the application runs in local development environment THEN the system SHALL CONTINUE TO support local filesystem storage for development purposes
