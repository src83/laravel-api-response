<?php

declare(strict_types=1);

namespace App\Modules\Earthquake\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Earthquake\Http\Requests\Api\EventRequest;
use App\Modules\Earthquake\Http\Resources\Api\EventResource;
use App\Modules\Earthquake\Repositories\EarthquakeRepository;
use Illuminate\Http\JsonResponse;
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Exceptions\ItemNotFoundException;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Http\Responses\ApiSuccessResponse;
use Src83\LaravelApiResponse\Support\Pagination\ApiPaginator;
use Symfony\Component\HttpFoundation\Response;

class EventsController extends Controller
{
    public function __construct(
        private readonly EarthquakeRepository $repository,
    ) {}

    public function index(EventRequest $request): JsonResponse
    {
        $page = $request->integer('page', 1);
        $perPage = (int) config('earthquake.items_per_page');

        $paginator = $this->repository->getListPaginated($page, $perPage);
        if ($paginator->isEmpty()) {
            return ApiSuccessResponse::make([]);
        }

        return ApiSuccessResponse::make(
            data: EventResource::collection($paginator),
            paginator: ApiPaginator::from($paginator),
        );
    }

    public function show(EventRequest $request, int $id): JsonResponse
    {
        if ($id === 13) {
            return ApiErrorResponse::make(
                Response::HTTP_CONFLICT,
                MessageKeyEnum::CONFLICT,
            );
        }

        $item = $this->repository->findById($id);

        if (!$item) {
            throw new ItemNotFoundException("Event with ID {$id} not found");
        }

        return ApiSuccessResponse::make(new EventResource($item));
    }
}
