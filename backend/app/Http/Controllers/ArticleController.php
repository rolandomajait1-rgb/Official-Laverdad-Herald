<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ArticleInteraction;
use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with('author.user', 'categories', 'tags');
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            // Require authentication for draft articles
            if ($request->status === 'draft') {
                $user = Auth::user();
                if (!$user || !$user->isAdmin()) {
                    return response()->json(['error' => 'Admin access required for drafts'], 403);
                }
            }
            $query->where('status', $request->status);
        } else {
            $query->published();
        }
        
        $query->latest($request->status === 'draft' ? 'created_at' : 'published_at');

        // Filter by category if provided
        if ($request->has('category') && $request->category) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('name', 'LIKE', $request->category);
            });
        }

        // Filter by limit if provided
        $limit = $request->get('limit', 10);
        $articles = $query->paginate($limit);

        if (request()->wantsJson()) {
            return response()->json($articles);
        }
        return view('articles.index', compact('articles'));
    }

    public function publicIndex(Request $request)
    {
        $limit = $request->get('limit', 10);
        $articles = Article::published()->with('author.user', 'categories', 'tags')->latest('published_at')->paginate($limit);
        
        if (request()->wantsJson()) {
            return response()->json($articles)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }
        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('articles.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'author_name' => 'required|string',
        ]);

        // Admin creates articles and assigns to authors
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        // Find user by name, then get their author profile
        $authorUser = User::where('name', $validated['author_name'])->first();
        if (!$authorUser) {
            return response()->json(['error' => 'Author user not found'], 404);
        }
        
        $author = Author::where('user_id', $authorUser->id)->first();
        if (!$author) {
            return response()->json(['error' => 'Author profile not found'], 404);
        }

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('articles', 'public');
        }

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Article::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $status = $request->get('status', 'published');
        $article = Article::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'author_id' => $author->id,
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
            'excerpt' => Str::limit($validated['content'], 150),
            'featured_image' => $imagePath,
        ]);

        $article->categories()->attach($validated['category_id']);

        if (!empty($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
                $tagIds[] = $tag->id;
            }
            $article->tags()->sync($tagIds);
        }

        return response()->json($article->load('author.user', 'categories', 'tags'), 201)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function show(Article $article)
    {
        try {
            if (request()->wantsJson()) {
                $article->load('author.user', 'categories', 'tags');
                return response()->json($article)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Credentials', 'true')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            }
            return view('articles.show', compact('article'));
        } catch (\Exception $e) {
            Log::error('Error in ArticleController@show: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load article: ' . $e->getMessage()], 500)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }
    }

    // Public-facing: show published article by slug
    public function publicShow(string $slug)
    {
        $article = Article::published()
            ->with('author.user', 'categories', 'tags')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = \App\Models\Article::published()
            ->where('id', '!=', $article->id)
            ->whereHas('categories', function($q) use ($article){
                $q->whereIn('categories.id', $article->categories->pluck('id'));
            })
            ->with('author.user')
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('articles.show', compact('article', 'related'));
    }

    public function edit(Article $article)
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('articles.edit', compact('article', 'categories', 'tags'));
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'tags' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'author' => 'required|string|min:1',
        ]);

        $oldValues = $article->toArray();

        // Authorize using policy (admins and moderators may update per policy)
        $this->authorize('update', $article);

        // Find user by name, then get their author profile
        $authorUser = User::where('name', $request->author)->first();
        if (!$authorUser) {
            return response()->json(['error' => 'Author user not found'], 404);
        }
        
        $author = Author::where('user_id', $authorUser->id)->first();
        if (!$author) {
            return response()->json(['error' => 'Author profile not found'], 404);
        }

        // Keep the original slug to maintain URL consistency
        $slug = $article->slug;

        // Update article data
        $data = [
            'title' => $request->title,
            'content' => $request->input('content'),
            'author_id' => $author->id,
            'slug' => $slug,
            'excerpt' => Str::limit($request->input('content'), 150),
        ];
        
        // Handle status update
        if ($request->has('status')) {
            $data['status'] = $request->status;
            if ($request->status === 'published' && !$article->published_at) {
                $data['published_at'] = now();
            }
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('articles', 'public');
        }

        $article->update($data);

        // Handle category
        if ($request->category) {
            $category = Category::firstOrCreate(['name' => $request->category]);
            $article->categories()->sync([$category->id]);
        }

        // Handle tags
        if ($request->tags) {
            $tags = explode(',', $request->tags);
            $tagIds = [];
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
                $tagIds[] = $tag->id;
            }
            $article->tags()->sync($tagIds);
        }

        // Logging removed as Log model is not available

        return response()->json($article->load('author.user', 'categories', 'tags'))
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function destroy(Article $article)
    {
        try {
            // Ensure user is authorized to delete (policy allows only admins)
            $this->authorize('delete', $article);

            $oldValues = $article->toArray();

            $article->delete();

            return response()->json(['message' => 'Article deleted successfully'])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete article: ' . $e->getMessage()], 500)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }
    }

    public function like(Article $article)
    {
        $existing = ArticleInteraction::where('user_id', Auth::id())
            ->where('article_id', $article->id)
            ->where('type', 'liked')
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['liked' => false, 'likes_count' => $article->interactions()->where('type', 'liked')->count()]);
        }

        ArticleInteraction::create([
            'user_id' => Auth::id(),
            'article_id' => $article->id,
            'type' => 'liked',
        ]);

        return response()->json(['liked' => true, 'likes_count' => $article->interactions()->where('type', 'liked')->count()]);
    }

    public function share(Article $article)
    {
        ArticleInteraction::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'article_id' => $article->id,
                'type' => 'shared',
            ],
            []
        );

        return back()->with('success', 'Article shared!');
    }

    public function getLikedArticles(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $articles = Article::whereHas('interactions', function ($query) {
            $query->where('user_id', Auth::id())
                  ->where('type', 'liked');
        })
        ->with('author.user', 'categories', 'tags')
        ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($articles)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function getSharedArticles(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $articles = Article::whereHas('interactions', function ($query) {
            $query->where('user_id', Auth::id())
                  ->where('type', 'shared');
        })
        ->with('author.user', 'categories', 'tags')
        ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($articles)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    public function getArticlesByAuthor(Request $request, $authorId)
    {
        $author = Author::find($authorId);
        if (!$author) {
            return response()->json(['error' => 'Author not found'], 404);
        }

        $query = Article::where('author_id', $authorId)->with('author.user', 'categories', 'tags');

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        $query->latest('published_at');

        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $articles = $query->paginate($perPage, ['*'], 'page', $page);

        // Add article count to response
        $articleCount = Article::where('author_id', $authorId)->count();

        return response()->json([
            'articles' => $articles,
            'article_count' => $articleCount,
            'author' => $author->load('user')
        ])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    // Public version of getArticlesByAuthor (no auth required)
    public function getArticlesByAuthorPublic(Request $request, $authorId)
    {
        $author = Author::find($authorId);
        if (!$author) {
            return response()->json(['error' => 'Author not found'], 404);
        }

        $query = Article::where('author_id', $authorId)->with('author.user', 'categories', 'tags');

        // Default to published only unless status specified
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        $query->latest('published_at');

        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $articles = $query->paginate($perPage, ['*'], 'page', $page);

        $articleCount = Article::where('author_id', $authorId)->count();

        return response()->json([
            'articles' => $articles,
            'article_count' => $articleCount,
            'author' => $author->load('user')
        ])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }
}
