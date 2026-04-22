<?php

namespace Modules\ShowDocManager\Models;

use Illuminate\Database\Eloquent\Model;

class ShowDocDirectory extends Model
{
    protected $table = 'showdoc_directories';

    protected $fillable = [
        'project_id',
        'cat_id',
        'name',
        'parent_cat_id',
        'level',
        'sort',
        'full_path'
    ];

    protected $casts = [
        'level' => 'integer',
        'sort' => 'integer',
    ];

    // 关联项目
    public function project()
    {
        return $this->belongsTo(ShowDocProject::class, 'project_id');
    }

    // 父目录
    public function parent()
    {
        return $this->belongsTo(ShowDocDirectory::class, 'parent_cat_id', 'cat_id');
    }

    // 子目录
    public function children()
    {
        return $this->hasMany(ShowDocDirectory::class, 'parent_cat_id', 'cat_id');
    }

    // 目录下的页面
    public function pages()
    {
        return $this->hasMany(ShowDocPage::class, 'directory_id');
    }
}
