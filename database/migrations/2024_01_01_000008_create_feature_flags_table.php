<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->boolean('enabled')->default(false);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feature_flags');
    }
};
