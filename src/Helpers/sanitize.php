<?php

/**
 * Input sanitization and validation helpers.
 *
 * These are thin wrappers that enforce consistent, explicit sanitization
 * throughout the application.  They do NOT replace prepared statements for
 * database queries – those must always be used independently.
 */

declare(strict_types=1);

/**
 * Return a trimmed string.  Returns an empty string if the value is not
 * a string or is not set.
 */
function inputString(mixed $value, int $maxLen = 1000): string
{
    if (!is_string($value)) {
        return '';
    }
    return mb_substr(trim($value), 0, $maxLen);
}

/**
 * Return an integer, or null if the value cannot be cast to a positive int.
 */
function inputPositiveInt(mixed $value): ?int
{
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int === false || $int < 1) {
        return null;
    }
    return $int;
}

/**
 * Return a validated e-mail address, or an empty string on failure.
 */
function inputEmail(mixed $value): string
{
    $clean = filter_var(trim((string)$value), FILTER_VALIDATE_EMAIL);
    return ($clean !== false) ? $clean : '';
}

/**
 * Return a validated numeric string (digits only), or empty string.
 * Useful for phone numbers and donor IDs.
 */
function inputNumericString(mixed $value, int $maxLen = 20): string
{
    $clean = preg_replace('/[^0-9]/', '', (string)$value);
    return mb_substr($clean, 0, $maxLen);
}

/**
 * Escape a value for safe HTML output.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Return a float from user input, or null when invalid.
 */
function inputFloat(mixed $value): ?float
{
    $float = filter_var($value, FILTER_VALIDATE_FLOAT);
    return ($float !== false) ? $float : null;
}
