CREATE TABLE bullets
(
  id          INT AUTO_INCREMENT PRIMARY KEY,
  x           INT                                       NOT NULL,
  y           INT                                       NOT NULL,
  r           INT                                       NOT NULL,
  playername  VARCHAR(255)                              NOT NULL,
  token       VARCHAR(255)                              NOT NULL,
  lastupdated TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6) NOT NULL,
  created     TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6) NOT NULL
);

CREATE TABLE leaderboard
(
  playername VARCHAR(255) NOT NULL,
  token      VARCHAR(255) NOT NULL,
  score      INT          NOT NULL,
  PRIMARY KEY (playername, token)
);

CREATE TABLE players
(
  playername  VARCHAR(255)                        NOT NULL PRIMARY KEY,
  token       VARCHAR(255)                        NOT NULL,
  x           INT                                 NOT NULL,
  y           INT                                 NOT NULL,
  r           INT                                 NOT NULL,
  lastupdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  score       INT DEFAULT '0'                     NOT NULL,
  dead        CHAR DEFAULT '0'                    NOT NULL,
  killedby    VARCHAR(255)                        NULL
);
