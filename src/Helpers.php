<?php
/**
 * Helpers - Funções utilitárias do sistema.
 * Validação de CNPJ, sanitização de input, formatação e utilidades gerais.
 */
class Helpers
{
    /**
     * Domínios de e-mail gratuitos bloqueados no cadastro.
     */
    private static array $blockedDomains = [
        'gmail.com', 'googlemail.com',
        'outlook.com', 'hotmail.com', 'live.com', 'msn.com',
        'yahoo.com', 'yahoo.com.br', 'ymail.com',
        'icloud.com', 'me.com', 'mac.com',
        'uol.com.br', 'bol.com.br', 'terra.com.br', 'ig.com.br',
        'protonmail.com', 'proton.me',
        'aol.com', 'zoho.com', 'mail.com', 'gmx.com',
    ];

    /**
     * Escapa output para prevenção de XSS.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitiza string de input (remove tags, trim).
     */
    public static function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }

    /**
     * Valida e-mail e bloqueia domínios gratuitos.
     * Retorna null se válido, ou mensagem de erro.
     */
    public static function validateCorporateEmail(string $email): ?string
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido.';
        }

        $domain = substr($email, strrpos($email, '@') + 1);
        if (in_array($domain, self::$blockedDomains, true)) {
            return 'Use um e-mail corporativo. Provedores gratuitos (Gmail, Outlook, etc.) não são aceitos.';
        }

        return null;
    }

    /**
     * Valida CNPJ pelo algoritmo oficial.
     * Retorna true se válido.
     */
    public static function validateCNPJ(string $cnpj): bool
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) return false;

        // Rejeita CNPJs com todos os dígitos iguais
        if (preg_match('/^(\d)\1+$/', $cnpj)) return false;

        // Validação do primeiro dígito verificador
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        if ((int)$cnpj[12] !== $digit1) return false;

        // Validação do segundo dígito verificador
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return (int)$cnpj[13] === $digit2;
    }

    /**
     * Formata CNPJ para exibição: XX.XXX.XXX/XXXX-XX
     */
    public static function formatCNPJ(string $cnpj): string
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        return preg_replace(
            '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/',
            '$1.$2.$3/$4-$5',
            $cnpj
        );
    }

    /**
     * Remove formatação do CNPJ (retorna apenas dígitos).
     */
    public static function cleanCNPJ(string $cnpj): string
    {
        return preg_replace('/\D/', '', $cnpj);
    }

    /**
     * Gera um token seguro aleatório.
     */
    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Verifica se uma data está no formato Y-m-d e é válida.
     */
    public static function validateDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Formata data para exibição em pt-BR: dd/mm/yyyy
     */
    public static function formatDate(string $date): string
    {
        if (empty($date)) return '';
        try {
            return (new DateTime($date))->format('d/m/Y');
        } catch (Exception $e) {
            return $date;
        }
    }

    /**
     * Formata telefone para exibição.
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $phone);
        }
        if (strlen($phone) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $phone);
        }
        return $phone;
    }

    /**
     * Retorna badge HTML de status de usuário.
     */
    public static function userStatusBadge(string $status): string
    {
        $map = [
            'pending_email' => ['Aguardando E-mail', 'bg-yellow-100 text-yellow-800'],
            'pending_admin' => ['Aguardando Aprovação', 'bg-orange-100 text-orange-800'],
            'active'        => ['Ativo', 'bg-green-100 text-green-800'],
        ];
        [$label, $class] = $map[$status] ?? ['Desconhecido', 'bg-gray-100 text-gray-800'];
        return "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$class}\">{$label}</span>";
    }

    /**
     * Redireciona para uma URL relativa ao APP_URL.
     */
    public static function redirect(string $path): never
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    /**
     * Define uma mensagem flash na sessão.
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Retorna e limpa a mensagem flash.
     */
    public static function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Retorna todas as mensagens flash e limpa.
     */
    public static function getAllFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}
