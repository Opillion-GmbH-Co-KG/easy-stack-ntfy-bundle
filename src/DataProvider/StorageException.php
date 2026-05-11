<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\DataProvider;

use Exception;
use Throwable;

final class StorageException extends Exception
{
    public function __construct(Throwable $exception, int $status = 0)
    {
        $code = $status > 0 ? $status : $exception->getCode();
        if ($code === 0) {
            $code = 500;
        }

        parent::__construct($exception->getMessage(), $code, $exception);
    }
}

