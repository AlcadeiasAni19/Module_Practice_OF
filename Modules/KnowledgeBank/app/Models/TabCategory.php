<?php

namespace Modules\KnowledgeBank\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\KnowledgeBank\Database\factories\TabCategoryFactory;

class TabCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'is_active'];

}
