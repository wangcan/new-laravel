<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('showdoc_directories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('showdoc_projects')->onDelete('cascade');
            $table->string('cat_id');                  // ShowDoc 目录ID
            $table->string('name');                    // 目录名称
            $table->string('parent_cat_id')->nullable(); // 父目录ID
            $table->integer('level')->default(0);      // 层级
            $table->integer('sort')->default(0);       // 排序
            $table->string('full_path')->nullable();   // 完整路径
            $table->timestamps();

            $table->unique(['project_id', 'cat_id']);
            $table->index('parent_cat_id');
            $table->index('level');
        });
    }

    public function down()
    {
        Schema::dropIfExists('showdoc_directories');
    }
};
