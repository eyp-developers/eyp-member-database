/* Create core database tables */

CREATE TABLE core_modules (
    name		VARCHAR(200) NOT NULL,
    title		VARCHAR(200),
    description	TEXT,
    version		INT NOT NULL,
    enabled		BOOL NOT NULL,
    PRIMARY KEY	(name)
);

CREATE TABLE core_models (
    module_name	VARCHAR(200) NOT NULL,
    name		VARCHAR(200) NOT NULL,
    PRIMARY KEY	(module_name, name)
);

CREATE TABLE core_models_fields (
	module_name	VARCHAR(200) NOT NULL,
    model_name	VARCHAR(200) NOT NULL,
    name		VARCHAR(200) NOT NULL,
    type		CHAR(10) NOT NULL,
    PRIMARY KEY	(module_name, model_name, name)
);

CREATE TABLE core_views (
	module_name	VARCHAR(200) NOT NULL,
	name		VARCHAR(200) NOT NULL,
	title		VARCHAR(200),
	type		VARCHAR(200) NOT NULL,
	datasource	VARCHAR(200),
	container	VARCHAR(200),
	in_sidebar	BOOL DEFAULT 0,
	PRIMARY KEY	(module_name, name)
);

CREATE TABLE core_views_fields (
	module_name	VARCHAR(200) NOT NULL,
	view_name	VARCHAR(200) NOT NULL,
	name		VARCHAR(200) NOT NULL,
	data_key	VARCHAR(200),
	title		VARCHAR(200),
	type		VARCHAR(200),
	target		VARCHAR(200),
	icon		VARCHAR(200),
	enabled		BOOL NOT NULL DEFAULT 1,
	visible		BOOL NOT NULL DEFAULT 1,
	view_order	INT NOT NULL DEFAULT 0,
	store_module	VARCHAR(200),
	store_name		VARCHAR(200),
	PRIMARY KEY	(module_name, view_name, name)
);

CREATE TABLE core_stores (
	name		VARCHAR(200) NOT NULL,
	module_name	VARCHAR(200) NOT NULL,
	model_name	VARCHAR(200) NOT NULL,
    data_key	VARCHAR(200) NOT NULL,
    value		VARCHAR(200) NOT NULL,
    PRIMARY KEY	(module_name, name)
);

/* Set foreign keys */
ALTER TABLE core_models ADD FOREIGN KEY (module_name) REFERENCES core_modules(name);
ALTER TABLE core_models_fields ADD FOREIGN KEY (module_name, model_name) REFERENCES core_models(module_name, name);
ALTER TABLE core_views ADD FOREIGN KEY (module_name) REFERENCES core_modules(name);
ALTER TABLE core_views_fields ADD FOREIGN KEY (module_name, view_name) REFERENCES core_views(module_name, name);
ALTER TABLE core_stores ADD FOREIGN KEY (module_name) REFERENCES core_modules(name);

/* Insert intallation data */

INSERT INTO core_modules VALUES('modules', 'Modules', 'A module to manage all other modules', 1, 1);
INSERT INTO core_modules VALUES('config', 'Config', 'A module to manage the system configuration', 1, 1);