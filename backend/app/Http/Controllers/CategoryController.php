<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $categories = Category::paginate(10);
        } else {
            $categories = Category::orderBy('name')->get();
        }

        if (request()->wantsJson()) {
            if ($categories instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                return response()->json([
                    'data' => $categories->items(),
                    'meta' => [
                        'current_page' => $categories->currentPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                        'last_page' => $categories->lastPage(),
                    ],
                    'links' => [
                        'first' => $categories->url(1),
                        'last' => $categories->url($categories->lastPage()),
                        'prev' => $categories->previousPageUrl(),
                        'next' => $categories->nextPageUrl(),
                    ],
                ]);
            }
            return response()->json($categories);
        }
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        $category = Category::create($data);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => 'Category',
            'model_id' => $category->id,
            'new_values' => $category->toArray(),
        ]);

        if (request()->wantsJson()) {
            return response()->json($category, 201);
        }
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        if (request()->wantsJson()) {
            return response()->json($category);
        }
        return view('categories.show', compact('category'));
    }

    // Public-facing: view category by slug with published articles
    public function publicShow(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $articles = \App\Models\Article::published()
            ->with('author.user', 'categories', 'tags')
            ->whereHas('categories', function ($q) use ($category) {
                $q->where('categories.id', $category->id);
            })
            ->latest('published_at')
            ->paginate(10);

        return view('categories.public', compact('category', 'articles'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $oldValues = $category->toArray();

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        $category->update($data);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => 'Category',
            'model_id' => $category->id,
            'old_values' => $oldValues,
            'new_values' => $category->toArray(),
        ]);

        if (request()->wantsJson()) {
            return response()->json($category);
        }
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $oldValues = $category->toArray();

        $category->delete();

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => 'Category',
            'model_id' => $category->id,
            'old_values' => $oldValues,
        ]);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Category deleted successfully']);
        }
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
