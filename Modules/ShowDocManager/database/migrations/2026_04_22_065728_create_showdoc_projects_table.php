<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('showdoc_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // 项目名称
            $table->string('showdoc_url');             // ShowDoc 实例地址
            $table->string('api_key');                 // API Key
            $table->string('api_token');               // API Token
            $table->string('project_id');              // ShowDoc 项目ID
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();   // 项目描述
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('showdoc_projects');
    }
};
