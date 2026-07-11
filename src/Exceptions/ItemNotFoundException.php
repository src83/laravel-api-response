<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/** @extends ModelNotFoundException<Model> */
class ItemNotFoundException extends ModelNotFoundException
{
    public function __construct(string $message = 'Item not found')
    {
        parent::__construct($message);
    }
}
