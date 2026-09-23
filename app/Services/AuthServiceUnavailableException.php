<?php

namespace App\Services;

use RuntimeException;

/**
 * Supabase Auth could not answer (network error, timeout, 5xx or rate limit).
 * Unlike a rejected token, this says nothing about the user's session, so
 * callers must not treat it as "logged out".
 */
class AuthServiceUnavailableException extends RuntimeException
{
}
