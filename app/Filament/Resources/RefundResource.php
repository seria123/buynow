<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundResource\Pages;
use App\Models\Sales\Refund;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?string $navigationLabel = 'Refunds';

    protected static ?string $modelLabel = 'Refund';

    protected static ?string $pluralModelLabel = 'Refunds';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Refund Details')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('order_id')
                                ->label('Order')
                                ->relationship('order', 'order_number')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('user_id')
                                ->label('Customer')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('amount')
                                ->label('Refund Amount')
                                ->prefix('KES')
                                ->numeric()
                                ->required(),
                            Select::make('refund_type')
                                ->label('Refund Type')
                                ->options([
                                    'full' => 'Full Refund',
                                    'partial' => 'Partial Refund',
                                ])
                                ->default('full')
                                ->required(),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'pending' => 'Pending',
                                    'processing' => 'Processing',
                                    'completed' => 'Completed',
                                    'failed' => 'Failed',
                                    'rejected' => 'Rejected',
                                ])
                                ->default('pending')
                                ->required(),
                        ]),
                    ]),
                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for Refund')
                            ->rows(3),
                        Textarea::make('notes')
                            ->label('Admin Notes')
                            ->rows(3),
                        TextInput::make('mpesa_transaction_id')
                            ->label('M-Pesa Transaction ID')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->prefix('KES ')
                    ->sortable(),
                \Filament\Tables\Columns\BadgeColumn::make('refund_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'full',
                        'warning' => 'partial',
                    ]),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => ['failed', 'rejected'],
                    ]),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'rejected' => 'Rejected',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('refund_type')
                    ->label('Type')
                    ->options([
                        'full' => 'Full',
                        'partial' => 'Partial',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('markProcessing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->visible(fn (Refund $record) => $record->status === 'pending')
                        ->action(fn (Refund $record) => $record->markAsProcessing()),
                    \Filament\Actions\Action::make('markCompleted')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Refund $record) => in_array($record->status, ['pending', 'processing']))
                        ->form([
                            TextInput::make('mpesa_transaction_id')
                                ->label('M-Pesa Transaction ID')
                                ->placeholder('e.g., RGX1234567'),
                        ])
                        ->action(function (Refund $record, array $data) {
                            $record->markAsCompleted($data['mpesa_transaction_id'] ?? null);
                        }),
                    \Filament\Actions\Action::make('markFailed')
                        ->label('Mark as Failed')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Refund $record) => in_array($record->status, ['pending', 'processing']))
                        ->form([
                            Textarea::make('notes')
                                ->label('Reason for Failure')
                                ->rows(3),
                        ])
                        ->action(function (Refund $record, array $data) {
                            $record->markAsFailed($data['notes'] ?? null);
                        }),
                    \Filament\Actions\Action::make('markRejected')
                        ->label('Reject Refund')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (Refund $record) => $record->status === 'pending')
                        ->form([
                            Textarea::make('notes')
                                ->label('Reason for Rejection')
                                ->rows(3),
                        ])
                        ->action(function (Refund $record, array $data) {
                            $record->markAsRejected($data['notes'] ?? null);
                        }),
                ]),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('markAsProcessing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->markAsProcessing()),
                    \Filament\Actions\BulkAction::make('markAsCompleted')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->markAsCompleted()),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefunds::route('/'),
            'create' => Pages\CreateRefund::route('/create'),
            'view' => Pages\ViewRefund::route('/{record}'),
            'edit' => Pages\EditRefund::route('/{record}/edit'),
        ];
    }
}
