<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('openaiKey');
            $table->string('model');
            $table->integer('temperature');
            $table->text('systemMsg');
            $table->string('userMsgColor');
            $table->string('aiMsgColor');
            $table->string('chatBubbleColor');
            $table->text('suggestedMsgs')->nullable();
            $table->string('initMsg')->nullable();
            $table->string('displayName');
            $table->string('chatIcon');
            $table->text('allowedDomains')->nullable();
            $table->string('password');
            $table->string('tgBotId')->nullable()->default(null);
            $table->string('host');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
