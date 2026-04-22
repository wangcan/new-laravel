<?php

namespace Modules\ShowDocManager\Models;

use Illuminate\Database\Eloquent\Model;

class ShowDocPage extends Model
{
    protected $table = 'showdoc_pages';

    protected $fillable = [
        'project_id',
        'directory_id',
        'page_id',
        'title',
        'content',
        'author',
        'version',
        'last_sync_at'
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    // 关联项目
    public function project()
    {
        return $this->belongsTo(ShowDocProject::class, 'project_id');
    }

    // 关联目录
    public function directory()
    {
        return $this->belongsTo(ShowDocDirectory::class, 'directory_id');
    }
}
