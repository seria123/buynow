<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make(),
                Action::make('submit_for_review')
                    ->label('Submit for Review')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn () => $this->record->status->value === 'draft' && $this->record->creator_id === Auth::id())
                    ->requiresConfirmation()
                    ->modalHeading('Submit Product for Review')
                    ->modalDescription('Are you sure you want to submit this product for review?')
                    ->action(function () {
                        $this->record->update(['status' => 'pending']);
                        $this->record->notifyAdminsForApproval();
                        $this->refreshFormData(['status']);
                    })
                    ->successNotificationTitle('Product submitted for review'),
                Action::make('request_for_review')
                    ->label('Request for Review')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn () => $this->record->status->value === 'rejected' && $this->record->creator_id === Auth::id())
                    ->requiresConfirmation()
                    ->modalHeading('Request Product Review')
                    ->modalDescription('Are you sure you want to request a review for this rejected product?')
                    ->action(function () {
                        $this->record->update(['status' => 'pending']);
                        $this->record->notifyAdminsForApproval();
                        $this->refreshFormData(['status']);
                    })
                    ->successNotificationTitle('Review requested for product'),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => $this->record->status->value === 'pending' && $this->record->creator_id !== Auth::id())
                    ->requiresConfirmation()
                    ->modalHeading('Approve Product')
                    ->modalDescription('Are you sure you want to approve this product?')
                    ->action(function () {
                        if ($this->record->creator_id === Auth::id()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('You cannot approve a product you created.')
                                ->danger()
                                ->send();

                            return;
                        }
                        $this->record->update([
                            'status' => 'approved',
                            'reviewer_id' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        $this->refreshFormData(['status', 'reviewer_id', 'reviewed_at']);
                    })
                    ->successNotificationTitle('Product approved'),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => $this->record->status->value === 'pending' && $this->record->creator_id !== Auth::id())
                    ->requiresConfirmation()
                    ->modalHeading('Reject Product')
                    ->modalDescription('Are you sure you want to reject this product?')
                    ->action(function () {
                        if ($this->record->creator_id === Auth::id()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('You cannot reject a product you created.')
                                ->danger()
                                ->send();

                            return;
                        }
                        $this->record->update([
                            'status' => 'rejected',
                            'reviewer_id' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        $this->refreshFormData(['status', 'reviewer_id', 'reviewed_at']);
                    })
                    ->successNotificationTitle('Product rejected'),
                Action::make('toggle_published')
                    ->label(fn () => $this->record->published ? 'Unpublish' : 'Publish')
                    ->icon(fn () => $this->record->published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn () => $this->record->published ? 'warning' : 'success')
                    ->visible(fn () => $this->record->status->value === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => $this->record->published ? 'Unpublish Product' : 'Publish Product')
                    ->modalDescription(fn () => $this->record->published
                        ? 'Are you sure you want to unpublish this product? It will no longer be visible to customers.'
                        : 'Are you sure you want to publish this product? It will be visible to customers.')
                    ->action(function () {
                        $this->record->update(['published' => ! $this->record->published]);
                        $this->refreshFormData(['published']);
                    })
                    ->successNotificationTitle(fn () => $this->record->published ? 'Product published' : 'Product unpublished'),
                DeleteAction::make(),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('md')
                ->color('primary')
                ->button(),
        ];
    }
}
