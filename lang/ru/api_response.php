<?php

/**
 * Словарь результатов выполнения операций
 * API возвращает сообщение с результатом из словаря api_response
 */

// RU
return [

    // Action-Based Success Messages

    'ok'      => 'Ok',
    'success' => 'Успешно',

    // CRUD BASE
    'created' => 'Запись успешно создана',
    'updated' => 'Запись успешно обновлена',
    'deleted' => 'Запись успешно удалена',

    // VISIBILITY / STATUS
    'published'   => 'Запись опубликована',
    'unpublished' => 'Запись снята с публикации',
    'activated'   => 'Запись активирована',
    'deactivated' => 'Запись деактивирована',
    'enabled'     => 'Функция включена',
    'disabled'    => 'Функция отключена',

    // CONFIRMATION / CHECK
    'checked'     => 'Проверка успешно выполнена',
    'verified'    => 'Успешно подтверждено',
    'validated'   => 'Данные успешно проверены',
    'invalidated' => 'Данные признаны недействительными',
    'approved'    => 'Запрос одобрен',
    'rejected'    => 'Запрос отклонён',

    // TRANSFER / IO
    'uploaded'   => 'Файл успешно загружен',
    'downloaded' => 'Файл успешно скачан',
    'imported'   => 'Данные импортированы',
    'exported'   => 'Данные экспортированы',

    // OTHER COMMON ACTIONS
    'sent'       => 'Отправлено',
    'received'   => 'Получено',
    'synced'     => 'Синхронизировано',
    'attached'   => 'Связано',
    'detached'   => 'Связь удалена',
    'assigned'   => 'Назначено',
    'unassigned' => 'Снято назначение',

    // HTTP Error Messages

    'http_error' => 'HTTP Error',

    'bad_request'           => 'Некорректный запрос',  // 400
    'unauthorized'          => 'Неаутентифицирован',  // 401
    'forbidden'             => 'Неавторизован (доступ запрещён)',  // 403
    'item_not_found'        => 'Запись не найдена',  // 404
    'model_not_found'       => 'Модель не найдена',  // 404
    'not_found'             => 'Не найдено',  // 404
    'method_not_allowed'    => 'Метод не поддерживается',  // 405
    'conflict'              => 'Конфликт',  // 409
    'content_too_large'     => 'Размер запроса превышает допустимый',  // 413
    'unprocessable_content' => 'Ошибка валидации',  // 422
    'locked'                => 'Ресурс заблокирован',  // 423
    'internal_server_error' => 'Внутренняя ошибка сервера',  // 500

    // Error Messages by Modules (check documentation)

];
