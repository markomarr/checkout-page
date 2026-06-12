<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Brand Owner';

    protected static ?string $modelLabel = 'Brand Owner';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->brandOwners()
            ->with(['brand' => fn ($query) => $query->withCount(['products', 'orders'])]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Login')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->helperText(fn (string $operation): string => $operation === 'edit'
                                ? 'Kosongkan jika tidak ingin mengubah password.'
                                : ''),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Brand')
                    ->schema([
                        Forms\Components\TextInput::make('brand_name')
                            ->label('Nama Brand')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'create') {
                                    $set('brand_slug', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('brand_slug')
                            ->label('Slug Brand')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: 'brands',
                                column: 'slug',
                                ignorable: fn (?User $record) => $record?->brand,
                            ),
                        Forms\Components\TextInput::make('brand_whatsapp_number')
                            ->label('Nomor WhatsApp Brand')
                            ->required()
                            ->maxLength(20),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand'),
                Tables\Columns\TextColumn::make('brand.products_count')
                    ->label('Produk'),
                Tables\Columns\TextColumn::make('brand.orders_count')
                    ->label('Order'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (User $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $newStatus = ! $record->is_active;

                        $record->update(['is_active' => $newStatus]);
                        $record->brand?->update(['is_active' => $newStatus]);

                        Notification::make()
                            ->title($newStatus ? 'Brand owner diaktifkan' : 'Brand owner dinonaktifkan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (User $record, Tables\Actions\DeleteAction $action): void {
                        if ($record->brand && $record->brand->orders()->exists()) {
                            Notification::make()
                                ->title('Tidak bisa menghapus')
                                ->body('Brand owner ini memiliki order aktif dan tidak bisa dihapus. Nonaktifkan saja.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
