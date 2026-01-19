<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Hero Section - Main Category Info
                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Category Name')
                                    ->weight('bold')
                                    ->icon('heroicon-o-tag')
                                    ->iconColor('primary')
                                    ->copyable()
                                    ->copyMessage('Name copied!')
                                    ->columnSpan(2),

                                IconEntry::make('is_active')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->columnSpan(1),
                            ]),

                        TextEntry::make('slug')
                            ->label('URL Slug')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-link')
                            ->copyable()
                            ->copyMessage('Slug copied!')
                            ->formatStateUsing(fn ($state) => '/'.$state),

                        TextEntry::make('sort_order')
                            ->label('Display Order')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-arrows-up-down')
                            ->suffix(' position')
                            ->tooltip('Lower numbers appear first in listings'),
                    ])
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary')
                    ->collapsible(false),

                // Two Column Layout for Hierarchy and Metadata
                Grid::make(2)
                    ->schema([
                        // Left Column - Hierarchy Information
                        Section::make('Category Hierarchy')
                            ->schema([
                                TextEntry::make('parent.name')
                                    ->label('Parent Category')
                                    ->icon('heroicon-o-folder')
                                    ->iconColor('gray')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('None (Top Level Category)')
                                    ->default('—'),

                                TextEntry::make('hierarchy_breadcrumb')
                                    ->label('Hierarchy Path')
                                    ->icon('heroicon-o-arrow-right')
                                    ->iconColor('info')
                                    ->html()
                                    ->formatStateUsing(function ($state, $record) {
                                        // Build breadcrumb from root to current category
                                        $ancestors = [];
                                        $current = $record;
                                        while ($current) {
                                            array_unshift($ancestors, $current);
                                            $current = $current->parent;
                                        }
                                        $badges = collect($ancestors)->map(function ($cat, $i) use ($record) {
                                            $isCurrent = $cat->id === $record->id;
                                            $color = $isCurrent ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800';

                                            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '.$color.' mr-2 mb-2">'.htmlspecialchars($cat->name).'</span>';
                                        })->implode('<span class="mx-1 text-gray-400">→</span>');

                                        return $badges;
                                    })
                                    ->default('—'),

                                TextEntry::make('children')
                                    ->label('Subcategories')
                                    ->icon('heroicon-o-folder-open')
                                    ->iconColor('info')
                                    ->formatStateUsing(function ($state, $record) {
                                        $children = $record->children ?? collect();
                                        $count = $children->count();
                                        if ($count === 0) {
                                            return '<span class="text-gray-500 italic">No subcategories</span>';
                                        }
                                        $badges = $children->map(function ($child) {
                                            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 mr-2 mb-2">'.htmlspecialchars($child->name).'</span>';
                                        })->implode('');

                                        return '<div class="mb-2"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">'.$count.' subcategor'.($count === 1 ? 'y' : 'ies').'</span></div>'
                                            .'<div class="flex flex-wrap">'.$badges.'</div>';
                                    })
                                    ->html(),

                                TextEntry::make('depth_level')
                                    ->label('Hierarchy Level')
                                    ->icon('heroicon-o-bars-3-bottom-left')
                                    ->badge()
                                    ->color('primary')
                                    ->formatStateUsing(function ($state, $record) {
                                        $level = 0;
                                        $current = $record;
                                        while ($current->parent) {
                                            $level++;
                                            $current = $current->parent;
                                        }

                                        return 'Level '.$level.($level === 0 ? ' (Root)' : '');
                                    }),
                            ])
                            ->icon('heroicon-o-chart-bar')
                            ->iconColor('info')
                            ->collapsible(),

                        // Right Column - Metadata
                        Section::make('Metadata')
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Category ID')
                                    ->icon('heroicon-o-hashtag')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable()
                                    ->copyMessage('ID copied!'),

                                TextEntry::make('creator.name')
                                    ->label('Created By')
                                    ->icon('heroicon-o-user')
                                    ->iconColor('primary')
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('Unknown')
                                    ->default('—'),

                                TextEntry::make('created_at')
                                    ->label('Created Date')
                                    ->icon('heroicon-o-calendar')
                                    ->iconColor('success')
                                    ->formatStateUsing(function ($state) {
                                        if (! $state) {
                                            return '—';
                                        }
                                        $carbon = \Carbon\Carbon::parse($state);
                                        $date = $carbon->format('l, F j, Y');
                                        $time = $carbon->format('g:i A');
                                        $relative = $carbon->diffForHumans();

                                        return '<div class="space-y-1">'.
                                            '<div class="font-semibold text-base">'.$date.'</div>'.
                                            '<div class="text-sm text-gray-600 dark:text-gray-400">'.$time.'</div>'.
                                            '<div class="text-xs text-gray-500 dark:text-gray-500 italic">'.$relative.'</div>'.
                                            '</div>';
                                    })
                                    ->html(),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->icon('heroicon-o-clock')
                                    ->iconColor('warning')
                                    ->formatStateUsing(function ($state) {
                                        if (! $state) {
                                            return '—';
                                        }
                                        $carbon = \Carbon\Carbon::parse($state);
                                        $date = $carbon->format('l, F j, Y');
                                        $time = $carbon->format('g:i A');
                                        $relative = $carbon->diffForHumans();

                                        return '<div class="space-y-1">'.
                                            '<div class="font-semibold text-base">'.$date.'</div>'.
                                            '<div class="text-sm text-gray-600 dark:text-gray-400">'.$time.'</div>'.
                                            '<div class="text-xs text-gray-500 dark:text-gray-500 italic">'.$relative.'</div>'.
                                            '</div>';
                                    })
                                    ->html(),
                            ])
                            ->icon('heroicon-o-document-text')
                            ->iconColor('warning')
                            ->collapsible(),
                    ]),

                // Statistics Section (now includes hierarchy path)
                Section::make('Category Statistics')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('products_count')
                                    ->label('Products')
                                    ->default(0)
                                    ->icon('heroicon-o-cube')
                                    ->iconColor('info')
                                    ->badge()
                                    ->color('info')
                                    ->numeric()
                                    ->formatStateUsing(fn ($state) => number_format($state ?? 0)),

                                TextEntry::make('children_count')
                                    ->label('Direct Subcategories')
                                    ->default(0)
                                    ->icon('heroicon-o-folder-open')
                                    ->iconColor('success')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(function ($state, $record) {
                                        $count = $record->children->count() ?? 0;

                                        return number_format($count);
                                    }),

                                TextEntry::make('total_descendants')
                                    ->label('Total Descendants')
                                    ->default(0)
                                    ->icon('heroicon-o-chart-bar')
                                    ->iconColor('primary')
                                    ->badge()
                                    ->color('primary')
                                    ->formatStateUsing(function ($state, $record) {
                                        // Recursively count all descendants
                                        $count = 0;
                                        $countDescendants = function ($category) use (&$countDescendants, &$count) {
                                            foreach ($category->children as $child) {
                                                $count++;
                                                $countDescendants($child);
                                            }
                                        };
                                        $countDescendants($record);

                                        return number_format($count);
                                    }),

                                TextEntry::make('status_label')
                                    ->label('Visibility')
                                    ->badge()
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->is_active ? 'Published' : 'Hidden';
                                    })
                                    ->color(fn ($record) => $record->is_active ? 'success' : 'danger')
                                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                                    ->iconColor(fn ($record) => $record->is_active ? 'success' : 'danger'),
                            ]),
                    ])
                    ->icon('heroicon-o-chart-pie')
                    ->iconColor('purple')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
