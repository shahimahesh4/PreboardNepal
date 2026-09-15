<?php

namespace App\Filament\Resources\Exams;

use App\Actions\Content\PublishExam;
use App\Filament\Resources\Exams\Pages\ManageExams;
use App\Models\AuditEntry;
use App\Models\Exam;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Assessments';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')->relationship('subject', 'name')->required(),
            TextInput::make('title')->required()->maxLength(200),
            TextInput::make('slug')->required()->maxLength(200)->unique(ignoreRecord: true)->regex('/^[a-z0-9-]+$/'),
            Textarea::make('description')->required()->maxLength(1000)->columnSpanFull(),
            TextInput::make('duration_minutes')->integer()->minValue(1)->maxValue(180)->default(10)->required(),
            Repeater::make('questions')->schema([
                Textarea::make('prompt')->required()->maxLength(3000)->columnSpanFull(),
                TextInput::make('options.0')->label('A')->required()->maxLength(500),
                TextInput::make('options.1')->label('B')->required()->maxLength(500),
                TextInput::make('options.2')->label('C')->required()->maxLength(500),
                TextInput::make('options.3')->label('D')->required()->maxLength(500),
                Select::make('correct')->label('Correct answer')->options([0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D'])->required()->in([0, 1, 2, 3]),
                Textarea::make('explanation')->required()->maxLength(5000)->columnSpanFull(),
            ])->columns(2)->minItems(1)->maxItems(100)->defaultItems(1)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->searchable(), TextColumn::make('subject.name'), TextColumn::make('duration_minutes'), TextColumn::make('version'), TextColumn::make('status')->badge()])->recordActions([EditAction::make()->mutateDataUsing(function (array $data, $record): array {
            $data['status'] = 'draft';
            $data['author_id'] = auth()->id();
            $data['reviewer_id'] = null;
            $data['reviewed_at'] = null;
            $data['version'] = $record->version + 1;

            return $data;
        }),
            Action::make('publish')->label('Review & publish')->icon('heroicon-o-check-circle')->requiresConfirmation()
                ->modalDescription('Confirm that you reviewed accuracy, rights, and curriculum alignment. You cannot approve your own work.')
                ->visible(fn ($record) => $record->status !== 'published' && $record->author_id !== auth()->id())
                ->action(function ($record) {
                    app(PublishExam::class)->handle(auth()->user(), $record);
                }),
            Action::make('withdraw')->color('danger')->requiresConfirmation()->visible(fn ($record) => $record->status === 'published')
                ->action(function ($record) {
                    $record->update(['status' => 'withdrawn']);
                    AuditEntry::create(['user_id' => auth()->id(), 'action' => 'content.withdrawn', 'resource' => 'Exam:'.$record->id]);
                }),
        ])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ManageExams::route('/')];
    }
}
