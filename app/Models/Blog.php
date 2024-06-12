<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;


class Blog extends Model
{
    use HasFactory;


    public function getCreatedAtAttribute($value){
        return date('d M Y',strtotime($value));
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeForUserWithCommentsCount($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->with(['comments' => function ($query) {
                $query->select('blog_id', 'user_id')
                      ->withCount('comments')
                      ->groupBy('blog_id', 'user_id');
            }])
            ->select('id', 'title');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('F d, Y');
    }
}
