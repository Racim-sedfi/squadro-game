-- Schéma SQLite de Squadro (lancement local sans Docker et tests d'intégration).

CREATE TABLE IF NOT EXISTS joueurs (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pseudo        TEXT    NOT NULL UNIQUE,
    mot_de_passe  TEXT    NOT NULL,
    cree_le       TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS parties (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    joueur_blanc_id  INTEGER NOT NULL REFERENCES joueurs (id) ON DELETE CASCADE,
    joueur_noir_id   INTEGER NULL     REFERENCES joueurs (id) ON DELETE CASCADE,
    statut           TEXT    NOT NULL,
    etat             TEXT    NOT NULL,
    version          INTEGER NOT NULL DEFAULT 0,
    cree_le          TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour_le    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_parties_statut ON parties (statut);
CREATE INDEX IF NOT EXISTS idx_parties_blanc ON parties (joueur_blanc_id);
CREATE INDEX IF NOT EXISTS idx_parties_noir ON parties (joueur_noir_id);
