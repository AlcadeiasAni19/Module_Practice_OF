<?php

namespace Modules\KnowledgeBank\app\Transformers;

use Modules\KnowledgeBank\app\Models\Content;

class ContentTransformer {
    public function transformAdminContentList (Content $content) {
        return [
            'title' => $content->title,
            'category' => $content->category->id,
            'is_nested' => $content->is_nested,
            'is_active' => $content->is_active
        ];
    }

    public function transformEndUserContentList (Content $content) {
        return [
            'title' => $content->title,
        ];
    }

    public function transformEndUserContentDetails (Content $content) {
        return [
            'title' => $content->title,
            'details' =>$content->details,
            'image' => $content->image
        ];
    }
}
