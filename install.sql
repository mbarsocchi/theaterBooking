
-- TODO link DATABASE so that if I delete the show, then delete PRENOTAZIONI

CREATE TABLE `spettacoli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` text NOT NULL,
  `luogo` text NOT NULL,
  `dettagli` text NOT NULL,
  `data` datetime NOT NULL,
  `posti` int NOT NULL DEFAULT '40',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `prenotazioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_spettacolo` int NOT NULL,
  `nome` text NOT NULL,
  `id_user_ref` int(11) NOT NULL,
  `_ins` datetime NOT NULL,
  `_upd` datetime DEFAULT NULL,
  `prenocode` text,
  PRIMARY KEY (`id`),
   UNIQUE KEY `id` (`id`),
  FOREIGN KEY (`id_spettacolo`) 
        REFERENCES `spettacoli` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_spettacolo`)
        REFERENCES spettacoli (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(512) NOT NULL,
  `user_login` varchar(512) NOT NULL,
  `password` varchar(40) NOT NULL,
  `access_level` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users_shows` (
  `id` int(11) NOT NULL  AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `show_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`show_id`)
        REFERENCES spettacoli (id)
        ON DELETE CASCADE,
  FOREIGN KEY (`user_id`)
        REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `auth_tokens` (
    `id` integer(11) not null AUTO_INCREMENT,
    `selector` char(12),
    `token` char(64),
    `userid` integer(11) not null,
    `expires` datetime,
    PRIMARY KEY (`id`),
  FOREIGN KEY (`userid`)
        REFERENCES users (id)
        ON DELETE CASCADE
);