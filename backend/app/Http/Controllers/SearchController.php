<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');
        $articles = collect();
        if (strlen($q) > 1) {
            $articles = Article::published()
                ->with('author.user', 'categories')
                ->where(function ($query) use ($q) {
                    $query->where('title', 'like', "%$q%")
                          ->orWhere('excerpt', 'like', "%$q%")
                          ->orWhere('content', 'like', "%$q%")
                          ->orWhereHas('author.user', function ($qry) use ($q) {
                              $qry->where('name', 'like', "%$q%");
                          })
                          ->orWhereHas('tags', function ($qry) use ($q) {
                              $qry->where('name', 'like', "%$q%");
                          });
                })
                ->latest('published_at')
                ->paginate(10);
        }

        return view('search.index', compact('articles', 'q'));
    }
}


