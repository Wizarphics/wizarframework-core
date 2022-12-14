<?php

namespace wizarphics\wizarframework\interfaces;

use wizarphics\wizarframework\auth\AuthResult;
use wizarphics\wizarframework\UserModel;

interface AuthenticationInterface
{
    public const FAILED_LOGIN = 'failed_login';
    public const LOGIN_EVENT = 'Loggedin';
    public const LOGOUT_EVENT = 'LoggedOut';
    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @throws \RuntimeException
     */
    public function attempt(array $credentials, bool $remember = false): AuthResult;

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     */
    public function check(array $credentials): AuthResult;

    /**
     * Checks if the user is currently logged in.
     */
    public function loggedIn(): bool;

    /**
     * Logs the given user in.
     * On success this must trigger the "login" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     */
    public function login(UserModel $user): void;

    /**
     * Logs a user in based on their ID.
     * On success this must trigger the "login" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     *
     * @param int|string $userId
     */
    public function loginById($userId): void;

    /**
     * Logs the current user out.
     * On success this must trigger the "logout" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     */
    public function logout(): void;

    /**
     * Returns the currently logged in user.
     */
    public function getUser(): ?UserModel;

    /**
     * Updates the user's last active date.
     */
    public function recordActiveDate(): void;
}
