<?php

declare(strict_types=1);

namespace AppUtils\HTMLHelper;

use AppUtils\BaseException;

class HTMLHelperException extends BaseException
{
    public const ERROR_CANNOT_FIND_CLOSING_TAG = 168601;
    public const ERROR_FORMAT_HTML_FAILED = 168602;
}
