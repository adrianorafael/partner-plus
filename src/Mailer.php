<?php
/**
 * Mailer - Envio de e-mails do sistema.
 * Usa a função mail() nativa do PHP com headers adequados.
 * Em produção, recomenda-se substituir por PHPMailer/SMTP.
 */
class Mailer
{
    /**
     * Envia um e-mail com corpo HTML.
     */
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        $from     = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@partnerplus.com.br';
        $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Partner Plus';

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        return mail($to, $subject, $htmlBody, $headers);
    }

    /**
     * E-mail de verificação de endereço.
     */
    public static function sendEmailVerification(string $to, string $name, string $token): bool
    {
        $link = APP_URL . '/verificar-email?token=' . urlencode($token);
        $subject = 'Confirme seu e-mail - Partner Plus';

        $body = self::layout($subject, "
            <h2 style='color:#06090F;'>Confirme seu e-mail, {$name}!</h2>
            <p>Obrigado por se cadastrar na <strong>Partner Plus</strong>. Clique no botão abaixo para confirmar seu endereço de e-mail:</p>
            <p style='text-align:center;margin:32px 0;'>
                <a href='{$link}' style='background:#00E5C8;color:#06090F;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;display:inline-block;'>
                    Confirmar E-mail
                </a>
            </p>
            <p style='color:#666;font-size:14px;'>Ou copie e cole este link no seu navegador:<br>
                <a href='{$link}' style='color:#00E5C8;'>{$link}</a>
            </p>
            <p style='color:#666;font-size:14px;'>Este link expira em <strong>24 horas</strong>.</p>
            <p style='color:#666;font-size:14px;'>Se você não criou esta conta, ignore este e-mail.</p>
        ");

        return self::send($to, $subject, $body);
    }

    /**
     * E-mail de notificação de aprovação de cadastro.
     */
    public static function sendApprovalNotification(string $to, string $name): bool
    {
        $link = APP_URL . '/entrar'  /* rota de login */;
        $subject = 'Cadastro aprovado - Partner Plus';

        $body = self::layout($subject, "
            <h2 style='color:#06090F;'>Seu cadastro foi aprovado, {$name}!</h2>
            <p>Ótima notícia! Seu cadastro na plataforma <strong>Partner Plus</strong> foi revisado e aprovado.</p>
            <p>Você já pode acessar a plataforma e começar a explorar as oportunidades disponíveis:</p>
            <p style='text-align:center;margin:32px 0;'>
                <a href='{$link}' style='background:#00E5C8;color:#06090F;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;display:inline-block;'>
                    Acessar Plataforma
                </a>
            </p>
        ");

        return self::send($to, $subject, $body);
    }

    /**
     * E-mail de reset de senha.
     */
    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $link = APP_URL . '/redefinir-senha?token=' . urlencode($token);
        $subject = 'Redefinição de senha - Partner Plus';

        $body = self::layout($subject, "
            <h2 style='color:#06090F;'>Redefinição de senha</h2>
            <p>Olá, <strong>{$name}</strong>. Recebemos uma solicitação para redefinir a senha da sua conta.</p>
            <p style='text-align:center;margin:32px 0;'>
                <a href='{$link}' style='background:#00E5C8;color:#06090F;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;display:inline-block;'>
                    Redefinir Senha
                </a>
            </p>
            <p style='color:#666;font-size:14px;'>Este link expira em <strong>1 hora</strong>.</p>
            <p style='color:#666;font-size:14px;'>Se você não solicitou a redefinição, ignore este e-mail. Sua senha permanece a mesma.</p>
        ");

        return self::send($to, $subject, $body);
    }

    /**
     * Notificação ao Admin de novo cadastro pendente.
     */
    public static function sendNewRegistrationAlert(string $adminEmail, string $userName, string $company): bool
    {
        $link = APP_URL . '/gerenciar-usuarios';
        $subject = 'Novo cadastro pendente - Partner Plus';

        $body = self::layout($subject, "
            <h2 style='color:#06090F;'>Novo cadastro pendente de aprovação</h2>
            <p>Um novo usuário completou o cadastro e está aguardando aprovação:</p>
            <table style='border-collapse:collapse;width:100%;margin:16px 0;'>
                <tr><td style='padding:8px;border:1px solid #eee;font-weight:bold;width:40%;'>Nome</td><td style='padding:8px;border:1px solid #eee;'>{$userName}</td></tr>
                <tr><td style='padding:8px;border:1px solid #eee;font-weight:bold;'>Empresa</td><td style='padding:8px;border:1px solid #eee;'>{$company}</td></tr>
            </table>
            <p style='text-align:center;margin:32px 0;'>
                <a href='{$link}' style='background:#00E5C8;color:#06090F;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;display:inline-block;'>
                    Gerenciar Usuários
                </a>
            </p>
        ");

        return self::send($adminEmail, $subject, $body);
    }

    /**
     * Template HTML base dos e-mails.
     */
    private static function layout(string $title, string $content): string
    {
        return "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width,initial-scale=1'>
    <title>{$title}</title>
</head>
<body style='margin:0;padding:0;background:#f5f5f5;font-family:\"DM Sans\",Arial,sans-serif;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f5f5;padding:40px 0;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>
                    <!-- Header -->
                    <tr>
                        <td style='background:#06090F;padding:24px 40px;text-align:center;'>
                            <span style='font-size:24px;font-weight:900;color:#00E5C8;letter-spacing:-0.5px;font-family:Roboto,Arial,sans-serif;'>Partner <span style='color:#B8FF45;'>Plus</span></span>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style='padding:40px;color:#06090F;line-height:1.6;'>
                            {$content}
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style='background:#f9f9f9;padding:20px 40px;text-align:center;border-top:1px solid #eee;'>
                            <p style='margin:0;color:#999;font-size:12px;'>© " . date('Y') . " Partner Plus. Todos os direitos reservados.</p>
                            <p style='margin:4px 0 0;color:#999;font-size:12px;'>Este é um e-mail automático, não responda a esta mensagem.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
    }
}
