<?php

namespace App\Filament\Resources\StudyDocuments;

use App\Actions\Content\PublishDocument;
use App\Filament\Resources\StudyDocuments\Pages\ManageStudyDocuments;
use App\Models\AuditEntry;
use App\Models\StudyDocument;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class StudyDocumentResource extends Resource
{
    protected static ?string $model = StudyDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('chapter_id')->relationship('chapter', 'title')->searchable()->preload()->required(),
            TextInput::make('title')->required()->maxLength(200),
            TextInput::make('slug')->required()->maxLength(200)->unique(ignoreRecord: true)->regex('/^[a-z0-9-]+$/'),
            Select::make('type')->options(['notes' => 'Chapter notes', 'worked_examples' => 'Worked examples', 'revision_guide' => 'Revision guide'])->required()->default('notes'),
            Select::make('language')->options(['English' => 'English', 'Nepali' => 'Nepali'])->required()->default('English'),
            TextInput::make('reading_minutes')->integer()->minValue(1)->maxValue(240)->default(5)->required(),
            Textarea::make('description')->required()->maxLength(1000)->columnSpanFull(),
            Textarea::make('preview')->required()->maxLength(5000)->helperText('Only this text is delivered without full access.')->columnSpanFull(),
            MarkdownEditor::make('body')->required()->fileAttachments(false)->columnSpanFull(),
            Textarea::make('rights_statement')->required()->maxLength(5000)->helperText('Record ownership or license evidence.')->columnSpanFull(),
            Toggle::make('is_free')->default(true)->helperText('Paid checkout is not enabled in this release.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->searchable()->limit(45), TextColumn::make('chapter.subject.name')->label('Subject'), TextColumn::make('status')->badge(), TextColumn::make('revision'), IconColumn::make('is_free')->boolean()])->recordActions([EditAction::make()->mutateDataUsing(function (array $data, $record): array {
            $data['status'] = 'draft';
            $data['author_id'] = auth()->id();
            $data['reviewer_id'] = null;
            $data['reviewed_at'] = null;
            $data['revision'] = $record->revision + 1;

            return $data;
        }),
            Action::make('publish')->label('Review & publish')->icon('heroicon-o-check-circle')->requiresConfirmation()
                ->modalDescription('Confirm that you reviewed accuracy, rights, and curriculum alignment. You cannot approve your own work.')
                ->visible(fn ($record) => $record->status !== 'published' && $record->author_id !== auth()->id())
                ->action(function ($record) {
                    app(PublishDocument::class)->handle(auth()->user(), $record);
                }),
            Action::make('withdraw')->color('danger')->requiresConfirmation()->visible(fn ($record) => $record->status === 'published')
                ->action(function ($record) {
                    $record->update(['status' => 'withdrawn']);
                    AuditEntry::create(['user_id' => auth()->id(), 'action' => 'content.withdrawn', 'resource' => 'StudyDocument:'.$record->id]);
                }),
        ])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ManageStudyDocuments::route('/')];
    }
}
