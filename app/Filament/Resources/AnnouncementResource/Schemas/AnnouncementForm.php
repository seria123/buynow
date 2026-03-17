<?php

namespace App\Filament\Resources\AnnouncementResource\Schemas;

use App\Models\CustomerGroup;
use App\Models\User;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content')
                    ->label('Content')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'bulletList',
                        'orderedList',
                        'link',
                    ]),
                Select::make('type')
                    ->label('Type')
                    ->required()
                    ->options([
                        'general' => 'General',
                        'promotional' => 'Promotional',
                        'order' => 'Order Related',
                        'account' => 'Account',
                        'newsletter' => 'Newsletter',
                    ])
                    ->default('general'),
                Radio::make('target')
                    ->label('Target Audience')
                    ->required()
                    ->options([
                        'all' => 'All Customers',
                        'customer_group' => 'Specific Customer Group',
                        'individual' => 'Individual Customer',
                    ])
                    ->default('all')
                    ->live(),
                Select::make('customer_group_id')
                    ->label('Customer Group')
                    ->options(fn() => CustomerGroup::active()->pluck('name', 'id'))
                    ->visible(fn(Get $get) => $get('target') === 'customer_group')
                    ->required(fn(Get $get) => $get('target') === 'customer_group'),
                Select::make('user_id')
                    ->label('Customer')
                    ->options(fn() => User::pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn(Get $get) => $get('target') === 'individual')
                    ->required(fn(Get $get) => $get('target') === 'individual'),
                DateTimePicker::make('scheduled_at')
                    ->label('Schedule for')
                    ->minDate(now())
                    ->placeholder('Leave empty to send immediately'),
                Actions::make([
                    Action::make('send_now')
                        ->label('Send Now')
                        ->visible(fn(Get $get) => !$get('scheduled_at'))
                        ->action(function (array $data, Form $form) {
                            $record = $form->getRecord();
                            $record->update([
                                'status' => 'draft',
                                'created_by' => auth()->id(),
                            ]);
                            $record->send();
                        })
                        ->requiresConfirmation()
                        ->color('primary'),
                ]),
                Placeholder::make('recipient_info')
                    ->label('Recipients')
                    ->content(function (Get $get) {
                        $target = $get('target');
                        $type = $get('type');
                        
                        $baseMessage = match ($target) {
                            'all' => 'This announcement will be sent to all customers.',
                            'customer_group' => 'This announcement will be sent to customers in the selected group.',
                            'individual' => 'This announcement will be sent to the selected customer.',
                            default => '',
                        };
                        
                        if (in_array($type, ['promotional', 'newsletter'])) {
                            $baseMessage .= ' Only customers who have opted in to marketing emails will receive this.';
                        }
                        
                        return $baseMessage;
                    }),
            ]);
    }
}
