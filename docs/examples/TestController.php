<?php

declare(strict_types=1);

namespace App\Modules\Test\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Test\DTO\TestDto;
use App\Modules\Test\Http\Requests\Api\TestRequest;
use App\Modules\Test\Http\Resources\Api\TestResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Exceptions\DomainLayerException;
use Src83\LaravelApiResponse\Exceptions\ItemNotFoundException;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Http\Responses\ApiPaginatedCollectionResponse;
use Src83\LaravelApiResponse\Http\Responses\ApiResponse;
use Src83\LaravelApiResponse\Http\Responses\ApiSuccessResponse;
use Src83\LaravelApiResponse\Support\Logging\BusinessLogger;
use Src83\LaravelApiResponse\Support\Pagination\ApiPaginator;
use Src83\LaravelApiResponse\Support\Pagination\ArrayPaginator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * @internal
 * @example
 *
 * Example controller demonstrating ApiResponse, pagination and DTO usage.
 * Not part of real API.
 *
 * Тестовый стенд для Debug-Mode API с примерами:
 * - Разных исключений
 * - Валидации структуры
 * - Кейсов пользовательских ошибок
 * - Покрытия бизнес-валидации и доменного слоя
 */
class TestController extends Controller
{
    private array $data;

    public function __construct()
    {
        $this->data = [
            ['id' => 1, 'name' => 'Test Model 01'],
            ['id' => 2, 'name' => 'Test Model 02'],
            ['id' => 3, 'name' => 'Test Model 03'],
        ];
    }

    /**
     * Пример / спецификация по ответам в методах контроллеров
     * Демонстрирует различные способы формирования ответов API
     * в зависимости от типа входных данных и стратегии пагинации.
     *
     * Input: array | Collection | DTO | Resource
     * Output: list response (paginated or not)
     *
     * @param TestRequest $request
     * @example Вывод массива без пагинации
     * return ApiSuccessResponse::make($items);
     *
     * @example Вывод коллекции DTO без пагинации
     * $data = collect($items)->map(fn ($item) => new TestDto($item['id'], $item['name']));
     * return ApiSuccessResponse::make($data);
     *
     * @example Вывод ресурса без пагинации
     * $data = collect($items)->map(fn ($item) => new TestDto($item['id'], $item['name']));
     * return ApiSuccessResponse::make(TestResource::collection($data));
     *
     * @example Вывод массива с пагинацией
     * $paginator = ArrayPaginator::paginate($items, 2, request('page', 1));
     * return ApiSuccessResponse::make(data: $paginator->items(), paginator: ApiPaginator::from($paginator));
     *
     * @example Вывод коллекции с пагинацией
     * $data = collect($items)->map(fn ($item) => new TestDto($item['id'], $item['name']));
     * $paginator = ArrayPaginator::paginate($data, 2, request('page', 1));
     * return ApiSuccessResponse::make(data: $paginator->items(), paginator: ApiPaginator::from($paginator));
     *
     * @example Вывод ресурса с пагинацией
     * $data = collect($items)->map(fn ($item) => new TestDto($item['id'], $item['name']));
     * $paginator = ArrayPaginator::paginate($data, 2, request('page', 1));
     * return ApiSuccessResponse::make(data: TestResource::collection($paginator), paginator: ApiPaginator::from($paginator));
     *
     * @example Вывод ресурса с пагинацией (через ещё одну коллекцию)
     * $data = collect($items)->map(fn ($item) => new TestDto($item['id'], $item['name']));
     * $paginator = ArrayPaginator::paginate($data, 2, request('page', 1));
     * $paginator->setCollection(TestResource::collection($paginator->items())->collection);
     * return ApiSuccessResponse::make(data: $paginator->items(), paginator: ApiPaginator::from($paginator));
     *
     * @example Вывод ресурса с пагинацией (через фабричный хелпер)
     * $data = collect($items)->map(fn ($item) => new TestDto($item['id'], $item['name']));
     * $paginator = ArrayPaginator::paginate($data, 2, request('page', 1));
     * return ApiPaginatedCollectionResponse::fromPaginator($paginator);
     *
     * @return JsonResponse
     */
    public function index(TestRequest $request): JsonResponse
    {
        $items = $this->data;

        $page = $request->integer('page');
        $perPage = 2;

        $data = collect($items)->map(
            fn($item) => new TestDto($item['id'], $item['name'])
        );

        if (!$page) {
            return ApiSuccessResponse::make(
                TestResource::collection($data)
            );
        }

        $paginator = ArrayPaginator::paginate($data, $perPage, $page);

        return ApiSuccessResponse::make(
            data: TestResource::collection($paginator),
            paginator: ApiPaginator::from($paginator)
        );
    }

    /**
     * Получить элемент по ID
     * @param TestRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(TestRequest $request, int $id): JsonResponse
    {
        $item = collect($this->data)->firstWhere('id', $id);

        if (!$item) {
            throw new ItemNotFoundException("Test model with ID {$id} not found");
        }

        return ApiSuccessResponse::make($item);
    }

    /**
     * Обновляем значение поля
     *
     * @example FYI: Есть отдельный особый случай: Ключ с точкой в начале -
     * удаляет из итогового ключа модуль по умолчанию, даже когда тот разрешён.
     *
     * @example Key with leading dot — strips module prefix even when is_module_available=true:
     * return ApiSuccessResponse::make(null, null, Response::HTTP_OK, '.updated');
     *
     * @param TestRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws DomainLayerException
     */
    public function update(TestRequest $request, int $id): JsonResponse
    {
        $item = collect($this->data)->firstWhere('id', $id);

        if (!$item) {
            throw new ItemNotFoundException("Test model with ID {$id} not found");
        }

        $name = $request->get('name');

        if (!$name) {
            app_terminate('name', 'Field has no value');
        }

        // Update...

        return ApiSuccessResponse::make(null, null, Response::HTTP_OK, 'updated');
    }

    public function store(TestRequest $request): JsonResponse
    {
        $data = [
            'id' => 42,
            'email' => $request->input('email'),
        ];

        return ApiSuccessResponse::make($data, null, Response::HTTP_CREATED, MessageKeyEnum::CREATED);
    }

    public function destroy(TestRequest $request, int $id): JsonResponse
    {
        $item = collect($this->data)->firstWhere('id', $id);

        if (!$item) {
            throw new ItemNotFoundException("Test model with ID {$id} not found");
        }

        if ($id === 3) {
            BusinessLogger::warning('already_deleted', [
                'user_id' => $id,
                'message' => 'this is destroy problem',
            ]);

            return ApiErrorResponse::make(
                Response::HTTP_CONFLICT,
                MessageKeyEnum::CONFLICT,
                null,
                'Запись заблокирована бизнес-логикой'
            );
        }

        $data = compact('id');

        return ApiSuccessResponse::make(data: $data, messageKey: MessageKeyEnum::DELETED);
    }

    /**
     * Exception showcase — uncomment the one you want to test.
     * @return void
     * @throws DomainLayerException
     */
    public function exception(): void
    {
        /**
         * Именованные исключения (без суффикса "Http")
         * Любое именованное, зарегистрированное в Handler::render()
         * 'message' в аргументе - это $sysMessage в Handler::render()
         * Ловятся либо именованной группой, либо 5XX
         * В именованных нет метода getStatusCode()
         */

        // 400: BadRequest - default message: ''
        #throw new BadRequestException();
        #throw new BadRequestHttpException();

        // 401: Authentication (unauthenticated) - default message: 'Unauthenticated.'
        #throw new AuthenticationException();

        // 403: Authorization (forbidden) - default message: 'This action is unauthorized.'
        #throw new AuthorizationException();
        #throw new AccessDeniedHttpException();
        #throw new UnauthorizedException();

        // 404: ItemNotFound - default message: 'Item not found'
        #throw new ItemNotFoundException();

        // 404: ModelNotFound - default message: ''
        #throw new ModelNotFoundException();

        // 404: NotFound - default message: ''
        #throw new NotFoundHttpException();

        // 405: MethodNotAllowed - default message: ''
        #throw new MethodNotAllowedException(['POST','PUT','PATCH'], 'The GET method is not supported.');
        #throw new MethodNotAllowedHttpException(['POST','PUT','PATCH'], 'The GET method is not supported.');

        // 409: Conflict / Business Conflict - default message: ''
        // Когда использовать:
        // * Попытка создать уже существующую сущность (дублирование email, username и т.п.).
        // * Конфликт версий (optimistic locking).
        // * Несогласованные бизнес-действия (“нельзя удалить, пока есть зависимые объекты”).
        #throw new ConflictHttpException();

        // 413: Request Entity Too Large - default message: ''
        #throw new PostTooLargeException();

        // 422: Validation - default message: ''
        throw ValidationException::withMessages([  // для нескольких полей
            'field_name' => ['Некорректное значение поля field_name.'],
            'email' => __('validation.email', ['attribute' => 'email']),
        ]);
        #app_terminate('field', 'Validation error description');  // без локализации
        #app_terminate('email', __('validation.email', ['attribute' => 'email']));

        // 423: Locked - default message: ''
        // Когда использовать:
        // * Объект находится в состоянии “в обработке”.
        // * Нельзя редактировать, пока другой процесс его не освободит.
        // * Есть блокировка по бизнес-логике (например, "заказ оплачен — редактирование запрещено").
        #throw new LockedHttpException();

        /** Неименованные исключения */

        #throw new HttpException(400, 'Bad Request');
        #throw new HttpException(401, 'Unauthenticated');
        #throw new HttpException(403, 'Forbidden');
        #throw new HttpException(404);
        #throw new HttpException(404, 'Not found');
        #throw new HttpException(405, 'Method Not Allowed');
        #throw new HttpException(409, 'Ошибка бизнес-валидации данных');
        #throw new HttpException(413, 'Content too large');
        #throw new HttpException(422, 'Validation error description');  // без указания поля
        #abort(422, 'Validation error description');  // без указания поля

        // 5XX - Системная непредвиденная ошибка:
        #throw new \RuntimeException('Неожиданная ошибка в бизнес-логике');

        // 5XX - Попытки ввести невалидный http-код:
        #throw new HttpException(1200, 'Random message');

        // Тоже можно, но не передаются словарные ключи и структуру данных надо контролировать более тщательно.
        #throw new HttpResponseException(response()->json(['user' => 'New user created'], 201));
        #throw new HttpResponseException(response()->json(['email' => 'Too long'], 400));
    }

    /**
     * Error return showcase — uncomment the one you want to test.
     * @return ApiResponse
     */
    public function getError(): ApiResponse
    {
        // 4XX - Типовые клиентские ошибки
        #return ApiResponse::error(400);
        #return ApiResponse::error(400, 'auth_registration.user_created', 'Gui message');
        #return ApiResponse::error(400, 'random_key', 'Gui message', 'Error description', ['id' => 123]);

        #return ApiErrorResponse::make(Response::HTTP_CONFLICT);
        #return ApiErrorResponse::make(Response::HTTP_CONFLICT, MessageKeyEnum::CONFLICT);
        #return ApiErrorResponse::make(Response::HTTP_CONFLICT, 'test.conflict');
        return ApiErrorResponse::make(Response::HTTP_CONFLICT, 'test.conflict', 'Запись заблокирована бизнес-логикой');

        // 5XX - Попытки ввести невалидный http-код:
        #return ApiResponse::error(500);
        #return ApiResponse::success([123], null, 1200);
        #return ApiSuccessResponse::make([123], null, 1200);
        #return ApiResponse::error(1200, 'random_key', 'Random message');
    }
}
