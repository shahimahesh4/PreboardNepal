<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student');
            $table->string('grade')->default('12');
            $table->string('locale')->default('en');
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description');
            $table->string('color')->default('blue');
            $table->string('symbol')->default('book');
            $table->string('curriculum')->default('Grade 12 · Foundation');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });
        Schema::create('study_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('type')->default('notes');
            $table->string('language')->default('English');
            $table->text('preview');
            $table->longText('body');
            $table->text('rights_statement');
            $table->string('status')->default('draft')->index();
            $table->boolean('is_free')->default(true);
            $table->unsignedInteger('reading_minutes')->default(5);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_document_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'study_document_id']);
        });
        Schema::create('reading_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_document_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at');
            $table->unique(['user_id', 'study_document_id']);
        });
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->unsignedInteger('duration_minutes')->default(15);
            $table->json('questions');
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('active')->index();
            $table->json('snapshot');
            $table->json('answers');
            $table->unsignedInteger('revision')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('deadline_at');
            $table->timestamp('saved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->unsignedInteger('total');
            $table->timestamps();
            $table->index(['user_id', 'exam_id', 'status']);
        });
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('study_document_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->text('message');
            $table->string('status')->default('open');
            $table->text('resolution')->nullable();
            $table->timestamps();
        });
        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source')->unique();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
        Schema::create('audit_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('resource');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['audit_entries', 'entitlements', 'content_reports', 'attempts', 'exams', 'reading_events', 'bookmarks', 'study_documents', 'chapters', 'subjects'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'grade', 'locale']));
    }
};
