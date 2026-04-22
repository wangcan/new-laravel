<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('showdoc_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('showdoc_projects')->onDelete('cascade');
            $table->foreignId('directory_id')->nullable()->constrained('showdoc_directories')->onDelete('set null');
            $table->string('page_id');                 // ShowDoc 页面ID
            $table->string('title');                   // 页面标题
            $table->longText('content')->nullable();   // 页面内容 (Markdown)
            $table->string('author')->nullable();      // 作者
            $table->string('version')->nullable();     // 版本
            $table->timestamp('last_sync_at')->nullable(); // 最后同步时间
            $table->timestamps();

            $table->unique(['project_id', 'page_id']);
            $table->index('title');
            $table->index('last_sync_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('showdoc_pages');
    }
};
