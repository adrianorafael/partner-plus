<?php
/**
 * CSRF - Proteção contra Cross-Site Request Forgery.
 * Gera e valida tokens únicos por sessão.
 */
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Retorna o token CSRF da sessão, gerando um novo se necessário.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Valida o token enviado pelo formulário.
     */
    public static function validate(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::TOKEN_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    /**
     * Renderiza o campo hidden com o token CSRF.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    /**
     * Valida e aborta com 403 se inválido. Usado no início de handlers POST.
     */
    public static function check(): void
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::validate($token)) {
            http_response_code(403);
            die('Requisição inválida. Token CSRF não corresponde.');
        }
    }
}
