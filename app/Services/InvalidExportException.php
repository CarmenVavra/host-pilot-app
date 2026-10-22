<?php

namespace App\Services;

use RuntimeException;

/**
 * The export file cannot be imported (wrong format, invalid data, …).
 */
class InvalidExportException extends RuntimeException {}
