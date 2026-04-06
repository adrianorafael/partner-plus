-- Partner Plus - Schema do Banco de Dados
-- Criado automaticamente pelo wizard de instalação.

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -------------------------------------------------------
-- Tabela: users
-- Armazena todos os usuários (admin, clientes, fornecedores)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cnpj`                 CHAR(14)       NOT NULL COMMENT 'Apenas dígitos',
    `company_name`         VARCHAR(255)   NOT NULL,
    `representative_name`  VARCHAR(255)   NOT NULL,
    `role`                 VARCHAR(100)   NOT NULL COMMENT 'Cargo do representante',
    `email`                VARCHAR(255)   NOT NULL UNIQUE,
    `phone`                VARCHAR(20)    NOT NULL,
    `password_hash`        VARCHAR(255)   NOT NULL,
    `type`                 ENUM('admin','client','provider') NOT NULL DEFAULT 'client',
    `status`               ENUM('pending_email','pending_admin','active') NOT NULL DEFAULT 'pending_email',
    `created_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cnpj`       (`cnpj`),
    INDEX `idx_type_status`(`type`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Tabela: email_verifications
-- Tokens de verificação de e-mail no cadastro
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_verifications` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `token`      CHAR(64)     NOT NULL UNIQUE,
    `expires_at` DATETIME     NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Tabela: password_resets
-- Tokens de reset de senha
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `token`      CHAR(64)     NOT NULL UNIQUE,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Tabela: opportunities
-- Oportunidades cadastradas pelos clientes
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `opportunities` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id`            INT UNSIGNED NOT NULL,
    `type`                 ENUM('software','service') NOT NULL,
    `title`                VARCHAR(255) NOT NULL,
    `description`          TEXT         NOT NULL,
    `start_date`           DATE         NOT NULL,
    `end_date`             DATE         NOT NULL,
    `target_provider`      VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nome do fornecedor específico, se direcionado',
    `contact_person_type`  ENUM('self','other') NOT NULL DEFAULT 'self',
    `contact_name`         VARCHAR(255) NULL,
    `contact_role`         VARCHAR(100) NULL,
    `contact_email`        VARCHAR(255) NULL,
    `contact_phone`        VARCHAR(20)  NULL,
    `status`               ENUM('active','closed') NOT NULL DEFAULT 'active',
    `created_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_client`    (`client_id`),
    INDEX `idx_status`    (`status`),
    INDEX `idx_end_date`  (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Tabela: opportunity_leads
-- Registro de quais fornecedores acessaram quais oportunidades
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `opportunity_leads` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `opportunity_id`  INT UNSIGNED NOT NULL,
    `provider_id`     INT UNSIGNED NOT NULL,
    `accessed_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`provider_id`)    REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_lead` (`opportunity_id`, `provider_id`),
    INDEX `idx_provider` (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
