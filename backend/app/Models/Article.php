<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'author_id',
        'author_name',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = ['featured_image_url'];
    
    public function getLikesCountAttribute()
    {
        // Use eager loaded count if available
        if (array_key_exists('likes_count', $this->attributes)) {
            return $this->attributes['likes_count'];
        }
        return $this->interactions()->where('type', 'liked')->count();
    }

    public function getIsLikedAttribute()
    {
        // Use eager loaded existence check if available
        if (array_key_exists('is_liked', $this->attributes)) {
            return (bool) $this->attributes['is_liked'];
        }
        
        if (Auth::check()) {
            return $this->interactions()->where('user_id', Auth::id())->where('type', 'liked')->exists();
        }
        return false;
    }

    public function getFeaturedImageUrlAttribute()
    {
        if (isset($this->attributes['featured_image']) && $this->attributes['featured_image']) {
            $path = $this->attributes['featured_image'];
            
            // Check if it's already a full URL (external or cloud storage)
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            
            // Generate the storage URL
            $url = config('app.url') . '/storage/' . $path;
            
            // Force HTTPS in production
            if (config('app.env') !== 'local' && !str_starts_with($url, 'https://')) {
                $url = str_replace('http://', 'https://', $url);
            }
            
            return $url;
        }
        
        // Return placeholder image if no featured image
        return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
    }
    


    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function interactions()
    {
        return $this->hasMany(ArticleInteraction::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $baseSlug = Str::slug($article->title);
                $slug = $baseSlug;
                $counter = 1;
                
                while (Article::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $article->slug = $slug;
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }
}
