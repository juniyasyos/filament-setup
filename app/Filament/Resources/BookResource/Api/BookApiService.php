<?php
namespace App\Filament\Resources\BookResource\Api;

use App\Filament\Resources\BookResource\Api\Handlers\CreateHandler;
use App\Filament\Resources\BookResource\Api\Handlers\UpdateHandler;
use App\Filament\Resources\BookResource\Api\Handlers\DeleteHandler;
use App\Filament\Resources\BookResource\Api\Handlers\PaginationHandler;
use App\Filament\Resources\BookResource\Api\Handlers\DetailHandler;
use Rupadana\ApiService\ApiService;
use App\Filament\Resources\BookResource;
use Illuminate\Routing\Router;


class BookApiService extends ApiService
{
    protected static string | null $resource = BookResource::class;

    public static function handlers() : array
    {
        return [
            CreateHandler::class,
            UpdateHandler::class,
            DeleteHandler::class,
            PaginationHandler::class,
            DetailHandler::class
        ];

    }
}
