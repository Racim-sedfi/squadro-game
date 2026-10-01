-- Schéma MySQL / MariaDB de Squadro.
-- Chargé automatiquement par le conteneur MySQL au premier démarrage (docker-entrypoint-initdb.d).

CREATE TABLE IF NOT EXISTS joueurs (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pseudo        VARCHAR(20)  NOT NULL,
    mot_de_passe  VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt/argon2 (password_hash)',
    cree_le       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_joueurs_pseudo UNIQUE (pseudo)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS parties (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    joueur_blanc_id  INT UNSIGNED NOT NULL,
    joueur_noir_id   INT UNSIGNED NULL,
    statut           VARCHAR(20)  NOT NULL COMMENT 'en_attente | en_cours | terminee',
    etat             JSON         NOT NULL COMMENT 'Plateau, trait, scores, dernier coup',
    version          INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Verrouillage optimiste',
    cree_le          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour_le    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_parties_blanc FOREIGN KEY (joueur_blanc_id) REFERENCES joueurs (id) ON DELETE CASCADE,
    CONSTRAINT fk_parties_noir  FOREIGN KEY (joueur_noir_id)  REFERENCES joueurs (id) ON DELETE CASCADE,
    INDEX idx_parties_statut (statut),
    INDEX idx_parties_blanc (joueur_blanc_id),
    INDEX idx_parties_noir (joueur_noir_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
