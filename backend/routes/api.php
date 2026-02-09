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
    
    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link'], 403);
    }
    
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified'], 200);
    }
    
    $user->markEmailAsVerified();
    
    return response()->json(['message' => 'Email verified successfully! You can now log in.'], 200);
})->name('verification.verify');
Route::options('/contact/feedback', function () {
    return response()->json([])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});
Route::middleware('throttle:10,1')->post('/contact/feedback', [ContactController::class, 'sendFeedback']);
Route::options('/contact/request-coverage', function () {
    return response()->json([])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});
Route::middleware('throttle:5,1')->post('/contact/request-coverage', [ContactController::class, 'requestCoverage']);
Route::options('/contact/join-herald', function () {
    return response()->json([])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});
Route::middleware('throttle:5,1')->post('/contact/join-herald', [ContactController::class, 'joinHerald']);
Route::options('/contact/subscribe', function () {
    return response()->json([])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});
Route::middleware('throttle:10,1')->post('/contact/subscribe', [ContactController::class, 'subscribe']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::options('/articles/public', function () {
    return response()->json([])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});
Route::get('/articles/public', [ArticleController::class, 'publicIndex']);
Route::get('/articles/search', function (Request $request) {
    $query = $request->get('q', '');
    
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
        
    return response()->json(['data' => $articles])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});
Route::get('/articles/by-slug/{slug}', function ($slug) {
    $article = Article::published()
        ->with('author.user', 'categories', 'tags')
        ->where('slug', $slug)
        ->firstOrFail();
        
    return response()->json($article)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

    // Public route to get articles by author id (paginated) and article count
    Route::get('/articles/author-public/{authorId}', [ArticleController::class, 'getArticlesByAuthorPublic']);

// Get all authors with their user names
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
    return response()->json($authors)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

// Public endpoint to fetch article by numeric ID (used by frontend fallback)
Route::get('/articles/id/{id}', function ($id) {
    $article = Article::with('author.user', 'categories', 'tags')->find($id);
    if (!$article) {
        return response()->json(['error' => 'Article not found'], 404)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    return response()->json($article)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

Route::get('/authors/{authorName}', function ($authorName) {
    Log::info('Looking for author/user: ' . $authorName);

    $user = \App\Models\User::where('name', $authorName)
        ->orWhere('email', $authorName)
        ->first();

    if (!$user) {
        return response()->json(['error' => 'Author not found'], 404)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    $author = \App\Models\Author::where('user_id', $user->id)->first();
    if (!$author) {
        return response()->json(['error' => 'Author profile not found'], 404)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
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
    ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

// Protected article routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    Route::post('/articles/{article}/like', [ArticleController::class, 'like']);
    Route::get('/articles/author/{authorId}', [ArticleController::class, 'getArticlesByAuthor']);
});

Route::options('/latest-articles', function () {
    return response()->json([])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});
Route::get('/latest-articles', function () {
    $articles = Article::published()
        ->with('author.user', 'categories')
        ->latest('published_at')
        ->take(6)
        ->get();
        
    return response()->json($articles)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

Route::get('/debug-articles', function () {
    $allArticles = Article::with('author.user', 'categories')->get();
    $publishedArticles = Article::published()->with('author.user', 'categories')->get();
    $draftArticles = Article::draft()->with('author.user', 'categories')->get();
    
    return response()->json([
        'total_articles' => $allArticles->count(),
        'published_articles' => $publishedArticles->count(),
        'draft_articles' => $draftArticles->count(),
        'all_articles' => $allArticles,
        'published_only' => $publishedArticles,
        'drafts_only' => $draftArticles
    ]);
});

// One-off route to merge duplicate authors for a given user.
// This is intended for local/dev use only. It will reassign articles from
// the duplicate author IDs to the chosen canonical author ID and then
// delete the duplicate author rows.
// (route removed) merge performed

Route::get('/test-drafts', function () {
    $draftArticles = Article::draft()->with('author.user', 'categories')->get();
    return response()->json($draftArticles)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

Route::put('/test-publish/{id}', function ($id) {
    $article = Article::findOrFail($id);
    $article->update(['status' => 'published', 'published_at' => now()]);
    return response()->json(['message' => 'Article published successfully'])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});



Route::get('/storage-link', function () {
    if (!file_exists(public_path('storage'))) {
        app('files')->link(
            storage_path('app/public'), public_path('storage')
        );
        return 'Storage link created!';
    }
    return 'Storage link already exists!';
});

Route::options('/categories/{category}/articles', function () {
    return response()->json([])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

Route::get('/categories/{category}/articles', function ($category) {
    $articles = Article::published()
        ->with('author.user', 'categories')
        ->whereHas('categories', function($q) use ($category) {
            $q->where('name', 'LIKE', $category);
        })
        ->latest('published_at')
        ->take(12)
        ->get();
        
    return response()->json(['data' => $articles])
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

// API Routes with Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    // User API
    Route::get('/user', function (Request $request) {
        return response()->json($request->user())
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    });

    // Logout API
    Route::post('/logout', [AuthController::class, 'logoutApi']);

    // Change Password API
    Route::post('/change-password', [AuthController::class, 'changePasswordApi']);

    // Delete Account API
    Route::post('/delete-account', [AuthController::class, 'deleteAccountApi']);

    // User liked and shared articles
    Route::get('/user/liked-articles', [ArticleController::class, 'getLikedArticles']);
    Route::get('/user/shared-articles', [ArticleController::class, 'getSharedArticles']);

    // Categories API
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Tags API
    Route::apiResource('tags', TagController::class);

    // Subscribers API
    Route::apiResource('subscribers', SubscriberController::class);

    // Logs API
    Route::get('/logs', [LogController::class, 'index']);
    Route::get('/logs/{log}', [LogController::class, 'show']);

    // Admin & Moderator shared routes
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
            ])
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
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
            
            return response()->json($activities)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
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

            return response()->json($logs)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        });
    });

    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/admin/check-access', function (Request $request) {
            return response()->json(['is_admin' => $request->user()->isAdmin()])
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        });

        Route::post('/admin/reset-data', function (Request $request) {
            \App\Models\Article::truncate();
            \App\Models\ArticleInteraction::truncate();
            \Illuminate\Support\Facades\DB::table('article_category')->truncate();
            \Illuminate\Support\Facades\DB::table('article_tag')->truncate();

            return response()->json(['message' => 'Data reset successfully'])
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
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
            ])
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        });

        Route::get('/admin/moderators', [\App\Http\Controllers\UserController::class, 'getModerators']);
        Route::post('/admin/moderators', [\App\Http\Controllers\UserController::class, 'addModerator']);
        Route::delete('/admin/moderators/{id}', [\App\Http\Controllers\UserController::class, 'removeModerator']);

        Route::apiResource('admin/users', \App\Http\Controllers\UserController::class);
        Route::apiResource('staff', StaffController::class);
        Route::apiResource('authors', AuthorController::class);
        Route::apiResource('users', UserController::class);
    });

    // Moderator API Routes
    Route::middleware(['role:moderator'])->group(function () {
        Route::apiResource('drafts', DraftController::class)->except(['store']);
    });

    // Author API Routes
    Route::middleware(['role:author'])->group(function () {
        Route::apiResource('drafts', DraftController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    });
});

