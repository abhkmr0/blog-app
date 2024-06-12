<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Blog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Comment extends Model
{
    use HasFactory;

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    protected $appends = ['formatted_created_at'];

    public function getFormattedCreatedAtAttribute()
    {
        \Log::info('getFormattedCreatedAtAttribute called');
        \Log::info($this->attributes['created_at']);
        return Carbon::parse($this->attributes['created_at'])->format('d-m-Y H:i A');
    }

}
