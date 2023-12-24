<?php

namespace Modules\KnowledgeBank\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title', 'category_id', 'is_active', 'is_nested', 'image', 'pdf', 'details'];

    public function category():BelongsTo {
        return $this->belongsTo(Category::class);
    }
}
