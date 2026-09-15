<?php

namespace Tests\Feature;

use App\Actions\Content\PublishDocument;
use App\Actions\Learning\AttemptService;
use App\Filament\Resources\AuditEntries\Pages\ManageAuditEntries;
use App\Filament\Resources\Chapters\Pages\ManageChapters;
use App\Filament\Resources\ContentReports\Pages\ManageContentReports;
use App\Filament\Resources\Exams\Pages\ManageExams;
use App\Filament\Resources\StudyDocuments\Pages\ManageStudyDocuments;
use App\Filament\Resources\Subjects\Pages\ManageSubjects;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Livewire\Library;
use App\Livewire\PracticeRunner;
use App\Livewire\Reader;
use App\Models\Attempt;
use App\Models\Entitlement;
use App\Models\Exam;
use App\Models\StudyDocument;
use App\Models\User;
use Database\Seeders\LearningSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class LearningPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningSeeder::class);
    }

    public function test_public_pages_render_and_private_pages_require_login(): void
    {
        foreach (['/', '/library', '/practice', '/plans', '/help', '/content-policy', '/login', '/register', '/subjects/physics'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/dashboard', '/saved', '/account'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_student_dashboard_account_and_saved_pages_render(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['/dashboard', '/account', '/saved'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_registration_does_not_accept_an_admin_role(): void
    {
        Notification::fake();
        $this->post('/register', ['name' => 'Learner', 'email' => 'learner@example.test', 'grade' => '11', 'password' => 'strongPass1234', 'password_confirmation' => 'strongPass1234', 'role' => 'admin'])->assertRedirect('/dashboard');
        $user = User::where('email', 'learner@example.test')->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertSame('11', $user->grade);
        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_registration_requires_a_supported_grade(): void
    {
        $this->post('/register', [
            'name' => 'Learner',
            'email' => 'learner@example.test',
            'grade' => '9',
            'password' => 'strongPass1234',
            'password_confirmation' => 'strongPass1234',
        ])->assertSessionHasErrors('grade');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'learner@example.test']);
    }

    public function test_signed_in_student_can_leave_demo_for_registration(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('register.switch'))
            ->assertRedirect(route('register'));

        $this->assertGuest();
        $this->get(route('register'))->assertOk()->assertSee('Create student dashboard');
    }

    public function test_search_filters_and_bookmarks_are_persistent_and_private(): void
    {
        $user = User::factory()->create();
        $doc = StudyDocument::first();
        Livewire::actingAs($user)->test(Library::class)->set('q', 'Electric circuits')->assertSee('Electric circuits, made clearer')->assertDontSee('A Punnett square, step by step')->call('toggleSave', $doc->id);
        $this->assertDatabaseHas('bookmarks', ['user_id' => $user->id, 'study_document_id' => $doc->id]);
        Livewire::actingAs($user)->test(Library::class, ['savedOnly' => true])->assertSee($doc->title);
        Livewire::actingAs(User::factory()->create())->test(Library::class, ['savedOnly' => true])->assertDontSee($doc->title);
    }

    public function test_locked_document_body_never_reaches_guest_html_or_livewire_payload(): void
    {
        $doc = StudyDocument::first();
        $doc->update(['is_free' => false, 'body' => 'TOP_SECRET_PAID_CONTENT', 'preview' => 'Visible preview']);
        $this->get(route('resources.show', $doc))->assertOk()->assertSee('Visible preview')->assertDontSee('TOP_SECRET_PAID_CONTENT');
        Livewire::test(Reader::class, ['document' => $doc])->assertDontSee('TOP_SECRET_PAID_CONTENT');
    }

    public function test_entitlements_expire_and_withdrawal_overrides_access(): void
    {
        $user = User::factory()->create();
        $doc = StudyDocument::first();
        $doc->update(['is_free' => false, 'body' => 'Entitled chapter content']);
        $pass = Entitlement::create(['user_id' => $user->id, 'source' => 'test:grant', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $this->actingAs($user)->get(route('resources.show', $doc))->assertSee('Entitled chapter content');
        $pass->update(['ends_at' => now()->subSecond()]);
        $this->get(route('resources.show', $doc))->assertDontSee('Entitled chapter content');
        $doc->update(['status' => 'withdrawn']);
        $this->get(route('resources.show', $doc))->assertNotFound();
    }

    public function test_saving_a_private_draft_by_id_is_rejected(): void
    {
        $doc = StudyDocument::first();
        $doc->update(['status' => 'draft']);
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs(User::factory()->create())->test(Library::class)->call('toggleSave', $doc->id);
    }

    public function test_attempt_resume_is_idempotent_and_results_use_original_snapshot(): void
    {
        $user = User::factory()->create();
        $exam = Exam::first();
        $service = app(AttemptService::class);
        $attempt = $service->start($user, $exam);
        $this->assertSame($attempt->id, $service->start($user, $exam)->id);
        $correct = (int) $attempt->snapshot['questions'][0]['correct'];
        $service->save($user, $attempt->id, 0, $correct, 0);
        $questions = $exam->questions;
        $questions[0]['correct'] = ($correct + 1) % 4;
        $exam->update(['questions' => $questions]);
        $result = $service->submit($user, $attempt->id);
        $this->assertSame(1, $result->score);
        $this->assertSame(1, $service->submit($user, $attempt->id)->score);
        $this->assertSame(1, Attempt::count());
    }

    public function test_stale_answer_write_is_rejected(): void
    {
        $user = User::factory()->create();
        $service = app(AttemptService::class);
        $attempt = $service->start($user, Exam::first());
        $service->save($user, $attempt->id, 0, 0, 0);
        $this->expectException(ValidationException::class);
        $service->save($user, $attempt->id, 0, 1, 0);
    }

    public function test_deadline_prevents_late_changes_and_finalizes_once(): void
    {
        $user = User::factory()->create();
        $service = app(AttemptService::class);
        $attempt = $service->start($user, Exam::first());
        $attempt->update(['deadline_at' => now()->subSecond()]);
        $result = $service->save($user, $attempt->id, 0, 0, 0);
        $this->assertSame('submitted', $result->status);
        $this->assertSame([], $result->answers);
        $this->assertSame(0, $result->score);
    }

    public function test_attempt_owner_and_answer_keys_are_protected(): void
    {
        $owner = User::factory()->create();
        $attempt = app(AttemptService::class)->start($owner, Exam::first());
        $this->actingAs($owner)->get(route('attempts.show', $attempt))->assertOk()->assertDontSee($attempt->snapshot['questions'][0]['explanation']);
        $this->actingAs(User::factory()->create())->get(route('attempts.show', $attempt))->assertNotFound();
    }

    public function test_livewire_attempt_selects_and_submits(): void
    {
        $user = User::factory()->create();
        $attempt = app(AttemptService::class)->start($user, Exam::first());
        $correct = (int) $attempt->snapshot['questions'][0]['correct'];
        Livewire::actingAs($user)->test(PracticeRunner::class, ['attempt' => $attempt])->call('choose', $correct)->assertSet('revision', 1)->call('submit')->assertSee('PRACTICE COMPLETE');
        $this->assertSame(1, $attempt->fresh()->score);
    }

    public function test_student_cannot_access_admin_and_author_cannot_self_publish(): void
    {
        $student = User::factory()->create();
        $this->assertFalse(Gate::forUser($student)->allows('viewAny', StudyDocument::class));
        $this->get('/admin')->assertNotFound();
        $this->actingAs($student)->get('/stnapanel')->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $doc = StudyDocument::first();
        $doc->update(['author_id' => $admin->id, 'status' => 'draft']);
        $this->expectException(ValidationException::class);
        app(PublishDocument::class)->handle($admin, $doc);
    }

    public function test_independent_review_publishes_and_records_audit(): void
    {
        $author = User::factory()->create(['role' => 'admin']);
        $reviewer = User::factory()->create(['role' => 'admin']);
        $doc = StudyDocument::first();
        $doc->update(['status' => 'draft', 'author_id' => $author->id]);
        app(PublishDocument::class)->handle($reviewer, $doc);
        $this->assertSame('published', $doc->fresh()->status);
        $this->assertDatabaseHas('audit_entries', ['action' => 'document.published', 'user_id' => $reviewer->id]);
    }

    public function test_reports_enter_staff_queue(): void
    {
        $user = User::factory()->create();
        $doc = StudyDocument::first();
        Livewire::actingAs($user)->test(Reader::class, ['document' => $doc])->set('category', 'accuracy')->set('message', 'Please review this explanation for a possible issue.')->call('report')->assertHasNoErrors();
        $this->assertDatabaseHas('content_reports', ['study_document_id' => $doc->id, 'user_id' => $user->id, 'status' => 'open']);
    }

    public function test_demo_login_is_not_available_outside_local_environment(): void
    {
        config(['preboard.demo_enabled' => true]);
        $this->post('/demo')->assertNotFound();
    }

    public function test_seed_can_run_twice_without_duplicate_content(): void
    {
        $this->seed(LearningSeeder::class);
        $this->assertSame(9, StudyDocument::count());
        $this->assertSame(3, Exam::count());
    }

    public function test_admin_management_tables_and_create_forms_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        foreach ([ManageSubjects::class, ManageChapters::class, ManageStudyDocuments::class, ManageExams::class] as $page) {
            Livewire::test($page)->assertOk()->call('mountAction', 'create')->assertOk();
        }
        foreach ([ManageUsers::class, ManageContentReports::class, ManageAuditEntries::class] as $page) {
            Livewire::test($page)->assertOk();
        }
    }
}
