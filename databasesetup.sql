/* Run this on your database with the following command:
 * mysql -u {DATABASEUSER} -p {DATABASENAME} < databasesetup.sql
 * Then enter the database user's password.
 */

CREATE TABLE IF NOT EXISTS users (
	id
		INT UNSIGNED
		NOT NULL
		AUTO_INCREMENT
		UNIQUE
	,
	
	username
		VARCHAR(64) /* disclaimer: more than changing this is needed to increase name limit */
		CHARACTER SET utf8
		NOT NULL
		UNIQUE
	,
	
	password
		BLOB(100) /* disclaimer: this might waste space? */
		NOT NULL
	,
	
	lastloginip
		VARCHAR(45) /* enough room for ipv6 addresses, if used?? */
		CHARACTER SET utf8
	,
	
	remembercookie
		VARCHAR(64) /* once implemented, will probably be a randomized string for remembering purposes? */
		CHARACTER SET utf8
	,
	
	role
		TINYINT UNSIGNED /* current setup is 0=user, 1=banned, 2=mod, but don't use enums because this could be expanded */
	,
	
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS records (
	id
		INT UNSIGNED
		NOT NULL
		AUTO_INCREMENT
		UNIQUE
	,
	
	user
		INT UNSIGNED
		NOT NULL
	,
	
	map /* hash */
		CHARACTER(32)
		CHARACTER SET utf8
		NOT NULL
	,
	
	`character`
		VARCHAR(16)
		CHARACTER SET utf8
		NOT NULL
	,
	
	playerbest
		TINYINT UNSIGNED
	,
	
	score
		INT UNSIGNED
	,
	
	time
		INT UNSIGNED
	,
	
	rings
		INT UNSIGNED
	,
	
	submitted
		INT UNSIGNED
	,
	
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS maps (
	id
		INT UNSIGNED
		NOT NULL
		AUTO_INCREMENT
		UNIQUE
	,
	
	hash
		CHARACTER(32)
		CHARACTER SET utf8
		NOT NULL
		UNIQUE
	,
	
	name
		VARCHAR(127)
		CHARACTER SET utf8
	,
	
	description
		TEXT(4096)
		CHARACTER SET utf8
	,
	
	type
		TINYINT UNSIGNED
	,
	
	levelpic
		VARCHAR(32)
		CHARACTER SET utf8
	,
	
	banned
		BOOLEAN
	,
	
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS characters (
	id
		INT UNSIGNED
		NOT NULL
		AUTO_INCREMENT
		UNIQUE
	,
	
	skin
		VARCHAR(16)
		CHARACTER SET utf8
		NOT NULL
		UNIQUE
	,
	
	name
		VARCHAR(127)
		CHARACTER SET utf8
	,
	
	description
		TEXT(4096)
		CHARACTER SET utf8
	,
	
	icon
		VARCHAR(32)
		CHARACTER SET utf8
	,
	
	banned
		BOOLEAN
	,
	
	PRIMARY KEY (id)
);