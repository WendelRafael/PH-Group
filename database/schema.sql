-- ============================================================
-- PH Catálogo — estrutura do banco (referência de desenvolvimento)
-- Para restaurar o banco COM dados, use database/dump.sql.
--
-- Executar:  mysql -u root < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS ph_catalogo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ph_catalogo;

-- Jogos importados da API FreeToGame (https://www.freetogame.com/api)
-- A chave primária é o próprio id da FreeToGame: reimportar nunca duplica.
CREATE TABLE IF NOT EXISTS games (
  id                     INT UNSIGNED  NOT NULL,
  title                  VARCHAR(255)  NOT NULL,
  thumbnail              VARCHAR(500)  NULL,
  short_description      TEXT          NULL,
  game_url               VARCHAR(500)  NULL,
  genre                  VARCHAR(100)  NULL,
  platform               VARCHAR(100)  NULL,
  publisher              VARCHAR(255)  NULL,
  developer              VARCHAR(255)  NULL,
  release_date           DATE          NULL,
  freetogame_profile_url VARCHAR(500)  NULL,
  created_at             TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP, -- quando entrou no catálogo

  PRIMARY KEY (id),
  KEY idx_genre (genre)
) ENGINE=InnoDB;
