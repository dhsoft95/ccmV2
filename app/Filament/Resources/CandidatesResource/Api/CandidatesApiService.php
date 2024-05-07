<?php
namespace App\Filament\Resources\CandidatesResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CandidatesResource;
use Illuminate\Routing\Router;


class CandidatesApiService extends ApiService
{
    protected static string | null $resource = CandidatesResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
