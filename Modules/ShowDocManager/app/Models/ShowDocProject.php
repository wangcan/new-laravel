<?php

namespace Modules\ShowDocManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShowDocProject extends Model
{
    use SoftDeletes;

    protected $table = 'showdoc_projects';

    protected $fillable = [
        'name',
        'showdoc_url',
        'api_key',
        'api_token',
        'project_id',
        'status',
        'description'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // 关联目录
    public function directories()
    {
        return $this->hasMany(ShowDocDirectory::class, 'project_id');
    }

    // 关联页面
    public function pages()
    {
        return $this->hasMany(ShowDocPage::class, 'project_id');
    }
}
