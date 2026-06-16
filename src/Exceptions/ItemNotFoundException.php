<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class ItemNotFoundException extends ModelNotFoundException
{
    public function __construct(string $message = 'Item not found')
    {
        parent::__construct($message);
    }
}
