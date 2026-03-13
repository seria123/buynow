<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('Image')
                    ->collection('thumbnail')
                    // ->circular()
                    ->size(50)
                    ->placeholder('No image'),

                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable(['name', 'slug', 'sku'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-cube')
                    ->iconColor('primary')
                    ->description(fn ($record) => $record->sku)
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Name copied!')
                    ->copyMessageDuration(1500),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-folder')
                    ->placeholder('—'),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-building-storefront')
                    ->placeholder('—'),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('KES')
                    ->sortable()
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('success'),

                TextColumn::make('quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(function ($state, $record) {
                        if ($record->hasVariants()) {
                            $totalStock = $record->getTotalStock();

                            return $totalStock > 0 ? 'success' : 'danger';
                        }

                        return $state > 0 ? 'success' : 'danger';
                    })
                    ->getStateUsing(function ($record) {
                        if ($record->hasVariants()) {
                            $totalStock = $record->getTotalStock();
                            $variantCount = $record->variants()->count();

                            return "{$totalStock} ({$variantCount} variants)";
                        }

                        return $record->quantity;
                    })
                    ->icon('heroicon-o-archive-box'),

                TextColumn::make('variants_count')
                    ->label('Variants')
                    ->counts('variants')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-squares-2x2')
                    ->default(0)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state->color())
                    ->icon(fn ($state) => $state->icon())
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),

                IconColumn::make('published')
                    ->label('Published')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn ($state) => $state ? 'Published' : 'Not Published'),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn ($state) => $state ? 'Featured' : 'Not Featured'),

                TextColumn::make('creator.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->badge()
                    ->placeholder('Unknown')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable()
                    ->color('info')
                    ->icon('heroicon-o-check-circle')
                    ->badge()
                    ->placeholder('Not reviewed')
                    ->description(fn ($record) => $record->reviewed_at ? $record->reviewed_at->format('M d, Y') : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '—';
                        }
                        $carbon = \Carbon\Carbon::parse($state);
                        $date = $carbon->format('M d, Y');
                        $relative = $carbon->diffForHumans();

                        return '<div class="space-y-0.5">'.
                            '<div class="font-semibold text-sm">'.$date.'</div>'.
                            '<div class="text-xs text-gray-500">'.$relative.'</div>'.
                            '</div>';
                    })
                    ->html()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('info')
                    ->tooltip(fn ($record) => $record->created_at?->format('l, F j, Y \a\t g:i A'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Review Status')
                    ->options([
                        ProductStatus::Draft->value => ProductStatus::Draft->label(),
                        ProductStatus::Pending->value => ProductStatus::Pending->label(),
                        ProductStatus::Approved->value => ProductStatus::Approved->label(),
                        ProductStatus::Rejected->value => ProductStatus::Rejected->label(),
                    ])
                    ->multiple(),

                TernaryFilter::make('published')
                    ->label('Published')
                    ->placeholder('All products')
                    ->trueLabel('Published only')
                    ->falseLabel('Unpublished only'),

                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All products')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    BulkAction::make('submit_for_review')
                        ->label('Submit for Review')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => ProductStatus::Pending]))
                        ->successNotificationTitle('Products submitted for review')
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => ProductStatus::Approved,
                                    'reviewer_id' => \Illuminate\Support\Facades\Auth::id(),
                                    'reviewed_at' => now(),
                                ]);
                            });
                        })
                        ->successNotificationTitle('Products approved')
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === ProductStatus::Approved) {
                                    $record->update(['published' => true]);
                                }
                            });
                        })
                        ->successNotificationTitle('Products published')
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['published' => false]))
                        ->successNotificationTitle('Products unpublished')
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete())
                        ->successNotificationTitle('Products deleted'),
                ]),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->tooltip('View product'),
                    \Filament\Actions\EditAction::make()
                        ->tooltip('Edit product'),
                    Action::make('submit_for_review')
                        ->label('Submit for Review')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->visible(fn ($record) => $record->status === ProductStatus::Draft && $record->creator_id === \Illuminate\Support\Facades\Auth::id())
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => ProductStatus::Pending]);
                            $record->notifyAdminsForApproval();
                        })
                        ->successNotificationTitle('Product submitted for review'),
                    Action::make('request_for_review')
                        ->label('Request for Review')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->visible(fn ($record) => $record->status === ProductStatus::Rejected && $record->creator_id === \Illuminate\Support\Facades\Auth::id())
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => ProductStatus::Pending]);
                            $record->notifyAdminsForApproval();
                        })
                        ->successNotificationTitle('Review requested for product'),
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === ProductStatus::Pending && $record->creator_id !== \Illuminate\Support\Facades\Auth::id())
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            if ($record->creator_id === \Illuminate\Support\Facades\Auth::id()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body('You cannot approve a product you created.')
                                    ->danger()
                                    ->send();

                                return;
                            }
                            $record->update([
                                'status' => ProductStatus::Approved,
                                'reviewer_id' => \Illuminate\Support\Facades\Auth::id(),
                                'reviewed_at' => now(),
                            ]);
                        })
                        ->successNotificationTitle('Product approved'),
                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === ProductStatus::Pending && $record->creator_id !== \Illuminate\Support\Facades\Auth::id())
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            if ($record->creator_id === \Illuminate\Support\Facades\Auth::id()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body('You cannot reject a product you created.')
                                    ->danger()
                                    ->send();

                                return;
                            }
                            $record->update([
                                'status' => ProductStatus::Rejected,
                                'reviewer_id' => \Illuminate\Support\Facades\Auth::id(),
                                'reviewed_at' => now(),
                            ]);
                        })
                        ->successNotificationTitle('Product rejected'),
                    Action::make('toggle_published')
                        ->label(fn ($record) => $record->published ? 'Unpublish' : 'Publish')
                        ->icon(fn ($record) => $record->published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn ($record) => $record->published ? 'warning' : 'success')
                        ->visible(fn ($record) => $record->status === ProductStatus::Approved)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['published' => ! $record->published]);
                        })
                        ->successNotificationTitle(fn ($record) => $record->published ? 'Product published' : 'Product unpublished'),
                    \Filament\Actions\DeleteAction::make()
                        ->tooltip('Delete product'),
                ]),
            ]);
    }
}
