<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Group as ComponentsGroup;
use Filament\Infolists\Components\Section as infoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function updateExpiredAndNotifiedDates($state, callable $set, $get)
    {
        $installedAt = $get('installed_at');
        if (!$installedAt) {
            return;
        }

        $date = Carbon::parse($installedAt);

        switch ($state) {
            case 0:
                $set('expired_at', $date->addWeek()->format('Y-m-d'));
                break;
            case 1:
                $set('expired_at', $date->addMonth()->format('Y-m-d'));
                break;
            case 2:
                $set('expired_at', $date->addMonths(3)->format('Y-m-d'));
                break;
            case 3:
                $set('expired_at', $date->addMonths(6)->format('Y-m-d'));
                break;
            case 4:
                $set('expired_at', $date->addYear()->format('Y-m-d'));
                break;
            case 5:
                $set('expired_at', $date->addYears(3)->format('Y-m-d'));
                break;
            case 6:
                $set('expired_at', null);
                $set('notified_at', null);
                break;
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Produk')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->placeholder('Masukkan Nama Produk')
                            ->minLength(3)
                            ->maxLength(50)
                            ->columnSpanFull()
                            ->required(),
                        Select::make('manufactur_id')
                            ->label('Manufaktur')
                            ->placeholder('Pilih Manufaktur')
                            ->relationship('manufactur', 'name')
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                        TextInput::make('lisence_number')
                            ->label('Nomor Lisensi')
                            ->placeholder('Masukkan Nomor Lisensi')
                            ->minLength(5)
                            ->maxLength(30)
                            ->required(),
                    ])->columns(2)
                    ->columnSpan(1),

                Section::make('Informasi Lisensi')
                    ->schema([
                        Select::make('duration')
                            ->label('Durasi Produk')
                            ->placeholder('Pilih Durasi Produk')
                            ->options([
                                0 => 'Durasi 1 Minggu',
                                1 => 'Durasi 1 Bulan',
                                2 => 'Durasi 3 Bulan',
                                3 => 'Durasi 6 Bulan',
                                4 => 'Durasi 1 Tahun',
                                5 => 'Durasi 3 Tahun',
                                6 => 'Permanen (Selamanya)',
                            ])
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->columnSpan(fn(Get $get): ?string => in_array($get('duration'), ['0', '1', '2', '3', '4', '5']) ? '1' : 'full')
                            ->live()
                            ->afterStateUpdated(
                                fn($state, callable $set, $get) =>
                                self::updateExpiredAndNotifiedDates($state, $set, $get)
                            )
                            ->required(),

                        Select::make('notified_at')
                            ->label('Waktu Notifikasi')
                            ->placeholder('Pilih Waktu Notifikasi')
                            ->options([
                                0 => 'Notifikasi Sebelum 1 Hari',
                                1 => 'Notifikasi Sebelum 1 Minggu',
                                2 => 'Notifikasi Sebelum 1 Bulan',
                            ])
                            ->default(null)
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->dehydrated()
                            ->dehydratedWhenHidden()
                            ->visible(fn(Get $get): bool => in_array($get('duration'), ['0', '1', '2', '3', '4', '5'])),

                        DatePicker::make('installed_at')
                            ->label('Tanggal Instalasi')
                            ->placeholder('Pilih Tanggal Instalasi')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(
                                fn($state, callable $set, $get) =>
                                self::updateExpiredAndNotifiedDates($get('duration'), $set, $get)
                            )
                            ->columnSpan(fn(Get $get): ?string => in_array($get('duration'), ['0', '1', '2', '3', '4', '5']) ? '1' : 'full')
                            ->required(),

                        DatePicker::make('expired_at')
                            ->label('Tanggal Kedaluarsa')
                            ->placeholder('Tanggal Kedaluarsa (Otomatis)')
                            ->hint('Otomatis')
                            ->disabled()
                            ->dehydrated()
                            ->dehydratedWhenHidden()
                            ->native(false)
                            ->visible(fn(Get $get): bool => in_array($get('duration'), ['0', '1', '2', '3', '4', '5'])),
                    ])->columns(2)
                    ->columnSpan(1),
            ])->columns(2);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                infoSection::make('')
                    ->schema([
                        ComponentsGroup::make([
                            Fieldset::make('Informasi Produk')
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('Nama Produk'),
                                    TextEntry::make('manufactur.name')
                                        ->label('Manufaktur'),
                                    TextEntry::make('lisence_number')
                                        ->label('Nomor Lisensi'),
                                ])->columns(3),
                        ])->columnSpan(5),
                        Fieldset::make('Informasi Lisensi')
                            ->schema([
                                TextEntry::make('duration')
                                    ->label('Durasi')
                                    ->badge()
                                    ->color('info')
                                    ->formatStateUsing(fn(int $state): string => match ($state) {
                                        0 => '1 Minggu',
                                        1 => '1 Bulan',
                                        2 => '3 Bulan',
                                        3 => '6 Bulan',
                                        4 => '1 Tahun',
                                        5 => '3 Tahun',
                                        6 => 'Permanen (Selamanya)',
                                    }),
                                TextEntry::make('notified_at')
                                    ->label('Waktu Notifikasi')
                                    ->placeholder('Tidak Ada Notifikasi')
                                    ->formatStateUsing(fn(int $state): string => match ($state) {
                                        0 => 'Sebelum 1 Hari',
                                        1 => 'Sebelum 1 Minggu',
                                        2 => 'Sebelum 1 Bulan',
                                    }),
                                TextEntry::make('installed_at')
                                    ->label('Tanggal Penginstallan')
                                    ->date(),
                                TextEntry::make('expired_at')
                                    ->label('Tanggal Kedaluarsa')
                                    ->date()
                                    ->placeholder('Tidak Kedaluarsa'),
                            ])->columnSpan(4),
                        Fieldset::make('Waktu Pembuatan')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Waktu data dibuat')
                                    ->since()
                                    ->dateTimeTooltip(),
                                TextEntry::make('updated_at')
                                    ->label('Waktu data diperbarui')
                                    ->since()
                                    ->dateTimeTooltip(),
                            ])->columns(1)
                            ->columnSpan(1),
                    ])->columns(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('manufactur.name')
                    ->label('Manufaktur')
            ])
            ->defaultGroup(
                Group::make('manufactur.name')
                    ->label('Manufaktur')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->description(fn(Model $record) => $record->manufactur->name)
                    ->searchable(),
                TextColumn::make('lisence_number')
                    ->label('Nomor Lisensi')
                    ->searchable(),
                TextColumn::make('duration')
                    ->label('Durasi')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        0 => '1 Minggu',
                        1 => '1 Bulan',
                        2 => '3 Bulan',
                        3 => '6 Bulan',
                        4 => '1 Tahun',
                        5 => '3 Tahun',
                        6 => 'Permanen (Selamanya)',
                    }),
                TextColumn::make('installed_at')
                    ->label('Tanggal Installasi')
                    ->date()
                    ->sortable(),
                TextColumn::make('expired_at')
                    ->label('Tanggal Kedaluarsa')
                    ->date()
                    ->placeholder('Permanen (Selamanya)'),
                TextColumn::make('notified_at')
                    ->label('Waktu Notifikasi')
                    ->placeholder('Tidak Ada Waktu.')
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        0 => 'Sebelum 1 Hari',
                        1 => 'Sebelum 1 Minggu',
                        2 => 'Sebelum 1 Bulan',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->color('info'),
                    DeleteAction::make(),
                ])
                    ->icon('heroicon-o-ellipsis-horizontal-circle')
                    ->color('info')
                    ->tooltip('Aksi')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
