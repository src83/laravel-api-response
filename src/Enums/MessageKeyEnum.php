<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Enums;

enum MessageKeyEnum: string
{
    // Action-Based Success Messages

    case OK = 'ok';
    case SUCCESS = 'success';

    // CRUD BASE
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';

    // VISIBILITY / STATUS
    case PUBLISHED = 'published';
    case UNPUBLISHED = 'unpublished';
    case ACTIVATED = 'activated';
    case DEACTIVATED = 'deactivated';
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';

    // CONFIRMATION / CHECK
    case CHECKED = 'checked';
    case VERIFIED = 'verified';
    case VALIDATED = 'validated';
    case INVALIDATED = 'invalidated';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    // TRANSFER / IO
    case UPLOADED = 'uploaded';
    case DOWNLOADED = 'downloaded';
    case IMPORTED = 'imported';
    case EXPORTED = 'exported';

    // OTHER COMMON ACTIONS
    case SENT = 'sent';
    case RECEIVED = 'received';
    case SYNCED = 'synced';
    case ATTACHED = 'attached';
    case DETACHED = 'detached';
    case ASSIGNED = 'assigned';
    case UNASSIGNED = 'unassigned';

    // Action-Based Error Messages

    case HTTP_ERROR = 'http_error';

    case BAD_REQUEST = 'bad_request';
    case UNAUTHORIZED = 'unauthorized';
    case FORBIDDEN = 'forbidden';
    case ITEM_NOT_FOUND = 'item_not_found';
    case MODEL_NOT_FOUND = 'model_not_found';
    case NOT_FOUND = 'not_found';
    case METHOD_NOT_ALLOWED = 'method_not_allowed';
    case CONFLICT = 'conflict';
    case CONTENT_TOO_LARGE = 'content_too_large';
    case UNPROCESSABLE_CONTENT = 'unprocessable_content';
    case LOCKED = 'locked';
    case INTERNAL_SERVER_ERROR = 'internal_server_error';
}
