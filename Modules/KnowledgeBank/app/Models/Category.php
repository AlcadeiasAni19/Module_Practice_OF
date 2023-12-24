<?php

namespace Modules\KnowledgeBank\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'is_nested', 'is_active', 'tab_category_id'];

    public function tab_category():BelongsTo {
        return $this->belongsTo(TabCategory::class);
    }

}
