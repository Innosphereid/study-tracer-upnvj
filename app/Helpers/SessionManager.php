<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class SessionManager
{
    /**
     * Flash a success message to the session
     *
     * @param string $message
     * @return void
     */
    public static function flashSuccess(string $message): void
    {
        Session::flash('success', $message);
    }

    /**
     * Flash an error message to the session
     *
     * @param string $message
     * @return void
     */
    public static function flashError(string $message): void
    {
        Session::flash('error', $message);
    }

    /**
     * Flash an info message to the session
     *
     * @param string $message
     * @return void
     */
    public static function flashInfo(string $message): void
    {
        Session::flash('info', $message);
    }
    
    /**
     * Flash a warning message to the session
     *
     * @param string $message
     * @return void
     */
    public static function flashWarning(string $message): void
    {
        Session::flash('warning', $message);
    }
    
    /**
     * Set an array of validation errors to the session
     *
     * @param array $errors
     * @return void
     */
    public static function setValidationErrors(array $errors): void
    {
        Session::flash('errors', $errors);
    }
}