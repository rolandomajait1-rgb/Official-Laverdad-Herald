<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\UserController;
use App\Models\Category;
use App\Models\Article;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TeamMemberController;

// Team Members Routes
Route::get('/team-members', [TeamMemberController::class, 'index']);
Route::middleware('auth:sanctum')->post('/team-members/update', [TeamMemberController::class, 'update']);

// Public API Routes with Rate Limiting
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'loginApi']);
    Route::post('/register', [AuthController::class, 'registerApi']);
});

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);
    
    $frontendUrl = config('app.frontend_url');

    if (!hash_equals((string) $hash, hash('sha256', $user->getEmailForVerification()))) {
        return redirect($frontendUrl . '/login?error=invalid_verification_link');
    }
    
    if ($user->hasVerifiedEmail()) {
        return redirect($frontendUrl . '/login?verified=1&message=already_verified');
    }
    
    $user->markEmailAsVerified();
    
    return redirect($frontendUrl . '/login?verified=1');
})->name('verification.verify');

Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail']);

// Contact Form Routes
Route::middleware('throttle:10,1')->post('/contact/feedback', [ContactController::class, 'sendFeedback']);
Route::middleware('throttle:5,1')->post('/contact/request-coverage', [ContactController::class, 'requestCoverage']);
Route::middleware('throttle:5,1')->post('/contact/join-herald', [ContactController::class, 'joinHerald']);
Route::middleware('throttle:10,1')->post('/contact/subscribe', [ContactController::class, 'subscribe']);

// Public Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}/articles', function ($category) {
    $articles = Article::published()
        ->with('author.user', 'categories')
        ->whereHas('categories', function($q) use ($category) {
            $q->where('name', 'LIKE', $category);
        })
        ->latest('published_at')
        ->take(12)
        ->get();
        
    return response()->json(['data' => $articles]);
});

// Public Articles
Route::get('/articles/public', [ArticleController::class, 'publicIndex']);
Route::get('/articles/search', function (Request $request) {
    $query = $request->get('q', '');
    
    // Sanitize search query to prevent wildcard abuse
    $query = str_replace(['%', '_'], ['\%', '\_'], $query);
    
    if (strlen(trim($query)) < 3) {
        return response()->json(['data' => []]);
    }
    
    $articles = Article::published()
        ->with('author.user', 'categories')
        ->where(function($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('content', 'LIKE', "%{$query}%")
              ->orWhere('excerpt', 'LIKE', "%{$query}%");
        })
        ->latest('published_at')
        ->take(20)
        ->get();
        
    return response()->json(['data' => $articles]);
});

Route::get('/articles/by-slug/{slug}', function ($slug) {
    $article = Article::published()
        ->with('author.user', 'categories', 'tags')
        ->where('slug', $slug)
        ->firstOrFail();
        
    return response()->json($article);
});

Route::get('/articles/id/{id}', function ($id) {
    $article = Article::with('author.user', 'categories', 'tags')->find($id);
    if (!$article) {
        return response()->json(['error' => 'Article not found'], 404);
    }

    return response()->json($article);
});

Route::get('/articles/author-public/{authorId}', [ArticleController::class, 'getArticlesByAuthorPublic']);

Route::get('/latest-articles', function () {
    $articles = Article::published()
        ->with('author.user', 'categories')
        ->latest('published_at')
        ->take(6)
        ->get();
        
    return response()->json($articles);
});

// Public Authors
Route::get('/authors', function () {
    $authors = \App\Models\Author::with('user:id,name,email')->get()->map(function($author) {
        return [
            'id' => $author->id,
            'name' => $author->name,
            'user_id' => $author->user_id,
            'bio' => $author->bio,
            'website' => $author->website,
        ];
    });
    return response()->json($authors);
});

Route::get('/authors/{authorName}', function ($authorName) {
    Log::info('Looking for author/user: ' . $authorName);

    $user = \App\Models\User::where('name', $authorName)
        ->orWhere('email', $authorName)
        ->first();

    if (!$user) {
        return response()->json(['error' => 'Author not found'], 404);
    }

    $author = \App\Models\Author::where('user_id', $user->id)->first();
    if (!$author) {
        return response()->json(['error' => 'Author profile not found'], 404);
    }

    $articles = Article::with('author.user', 'categories')
        ->where('author_id', $author->id)
        ->latest('created_at')
        ->get();

    $formattedArticles = $articles->map(function ($article) {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->content,
            'excerpt' => $article->excerpt,
            'image_url' => $article->featured_image_url,
            'category' => $article->categories->first()?->name ?? 'Uncategorized',
            'author' => $article->author->name,
            'created_at' => $article->created_at,
            'slug' => $article->slug,
            'status' => $article->status
        ];
    });

    return response()->json([
        'author' => [
            'name' => $user->name,
            'articleCount' => $formattedArticles->count()
        ],
        'articles' => $formattedArticles
    ]);
});

// Public Tags
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{tag}', [TagController::class, 'show']);

// Protected Logs (moved to auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logs', [LogController::class, 'index']);
    Route::get('/logs/{log}', [LogController::class, 'show']);
});

// Protected Article Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logoutApi']);
    Route::post('/change-password', [AuthController::class, 'changePasswordApi']);
    Route::post('/delete-account', [AuthController::class, 'deleteAccountApi']);

    Route::get('/user/liked-articles', [ArticleController::class, 'getLikedArticles']);
    Route::get('/user/shared-articles', [ArticleController::class, 'getSharedArticles']);

    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    Route::post('/articles/{article}/like', [ArticleController::class, 'like']);
    Route::get('/articles/author/{authorId}', [ArticleController::class, 'getArticlesByAuthor']);

    // Categories API
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Tags API
    Route::post('/tags', [TagController::class, 'store']);
    Route::put('/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

    // Subscribers API
    Route::apiResource('subscribers', SubscriberController::class);

    // Admin & Moderator Shared Routes
    Route::middleware(['role:admin,moderator'])->group(function () {
        Route::get('/admin/dashboard-stats', function (Request $request) {
            $users = \App\Models\User::count();
            $articles = \App\Models\Article::where('status', 'published')->count();
            $drafts = \App\Models\Article::where('status', 'draft')->count();
            $views = \App\Models\ArticleInteraction::where('type', 'shared')->count();
            $likes = \App\Models\ArticleInteraction::where('type', 'liked')->count();

            return response()->json([
                'users' => $users,
                'articles' => $articles,
                'drafts' => $drafts,
                'views' => $views,
                'likes' => $likes
            ]);
        });

        Route::get('/admin/recent-activity', function (Request $request) {
            $activities = [];
            
            $recentArticles = \App\Models\Article::with('author.user')
                ->where('status', 'published')
                ->latest('published_at')
                ->take(20)
                ->get();
            
            foreach ($recentArticles as $article) {
                $activities[] = [
                    'action' => 'Published',
                    'title' => $article->title,
                    'user' => $article->author->user->email ?? 'Unknown',
                    'timestamp' => $article->published_at->format('n/j/Y g:i A')
                ];
            }
            
            return response()->json($activities);
        });

        Route::get('/admin/audit-logs', function (Request $request) {
            $logs = \App\Models\Log::with('user')
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get()
                ->map(function ($log) {
                    return [
                        'action' => $log->action,
                        'article_title' => $log->model_type === 'App\\Models\\Article' ? \App\Models\Article::find($log->model_id)?->title : null,
                        'user_email' => $log->user?->email,
                        'created_at' => $log->created_at,
                    ];
                });

            return response()->json($logs);
        });
    });

    // Admin-Only Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/check-access', function (Request $request) {
            return response()->json(['is_admin' => $request->user()->isAdmin()]);
        });

        Route::get('/admin/stats', function (Request $request) {
            $totalArticles = \App\Models\Article::count();
            $totalUsers = \App\Models\User::count();
            $totalViews = \App\Models\ArticleInteraction::where('type', 'shared')->count();
            $recentArticles = \App\Models\Article::with('author.user', 'categories')
                ->latest('published_at')
                ->take(5)
                ->get();

            return response()->json([
                'totalArticles' => $totalArticles,
                'totalUsers' => $totalUsers,
                'totalViews' => $totalViews,
                'recentArticles' => $recentArticles
            ]);
        });

        Route::get('/admin/moderators', [\App\Http\Controllers\UserController::class, 'getModerators']);
        Route::post('/admin/moderators', [\App\Http\Controllers\UserController::class, 'addModerator']);
        Route::delete('/admin/moderators/{id}', [\App\Http\Controllers\UserController::class, 'removeModerator']);

        Route::apiResource('admin/users', \App\Http\Controllers\UserController::class);
        Route::apiResource('staff', StaffController::class);
    });

    // Moderator Routes
    Route::middleware(['role:moderator'])->group(function () {
        Route::apiResource('drafts', DraftController::class)->except(['store']);
    });

    // Author Routes
    Route::middleware(['role:author'])->group(function () {
        Route::apiResource('drafts', DraftController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    });
});
