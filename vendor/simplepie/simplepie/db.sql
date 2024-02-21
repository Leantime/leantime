/* SQLite */
CREATE TABLE cache_data (
	id TEXT NOT NULL,
	items SMALLINT NOT NULL DEFAULT 0,
	data BLOB NOT NULL,
	mtime INTEGER UNSIGNED NOT NULL
);
CREATE UNIQUE INDEX id ON cache_data(id);

CREATE TABLE items (
	feed_id TEXT NOT NULL,
	id TEXT NOT NULL,
	data TEXT NOT NULL,
	posted INTEGER UNSIGNED NOT NULL
);
CREATE INDEX feed_id ON items(feed_id);


/* MySQL */
--- Don't paste this to create tables, since SimplePie will create
--- tables by its own.
CREATE TABLE `cache_data` (
	`id` TEXT CHARACTER SET utf8 NOT NULL,
	`items` SMALLINT NOT NULL DEFAULT 0,
	`data` BLOB NOT NULL,
	`mtime` INT UNSIGNED NOT NULL,
	UNIQUE (
		`id`(125)
	)
);

CREATE TABLE `items` (
	`feed_id` TEXT CHARACTER SET utf8 NOT NULL,
	`id` TEXT CHARACTER SET utf8 NOT NULL,
	`data` MEDIUMBLOB NOT NULL,
	`posted` INT UNSIGNED NOT NULL,
	INDEX `feed_id` (
		`feed_id`(125)
	)
);