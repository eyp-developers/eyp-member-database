/* Create core database tables */

CREATE TABLE core_modules (
    module_name		VARCHAR(200) NOT NULL,
    module_title	VARCHAR(200),
    module_description	TEXT,
    module_version	INT NOT NULL,
    module_enabled	BOOL NOT NULL,
    PRIMARY KEY		(module_name)
);

CREATE TABLE core_models (
    module_name		VARCHAR(200) NOT NULL,
    table_name		VARCHAR(200) NOT NULL,
    PRIMARY KEY		(module_name, table_name)
);

CREATE TABLE core_models_fields (
	module_name		VARCHAR(200) NOT NULL,
    table_name		VARCHAR(200) NOT NULL,
    field_name		VARCHAR(200) NOT NULL,
    field_type		CHAR(10) NOT NULL,
    PRIMARY KEY		(module_name, table_name, field_name)
);

CREATE TABLE core_views (
	module_name		VARCHAR(200) NOT NULL,
	view_name		VARCHAR(200) NOT NULL,
	view_title		VARCHAR(200),
	view_type		VARCHAR(200) NOT NULL,
	view_datasource	VARCHAR(200),
	view_in_sidebar	BOOL DEFAULT 0,
	PRIMARY KEY		(module_name, view_name)
);

CREATE TABLE core_views_fields (
	module_name		VARCHAR(200) NOT NULL,
	view_name		VARCHAR(200) NOT NULL,
	field_name		VARCHAR(200) NOT NULL,
	field_key		VARCHAR(200),
	field_title		VARCHAR(200),
	field_type		VARCHAR(200) NOT NULL,
	field_target	VARCHAR(200),
	field_icon		VARCHAR(200),
	field_enabled	BOOL NOT NULL DEFAULT 1,
	field_visible	BOOL NOT NULL DEFAULT 1,
	field_order		INT NOT NULL DEFAULT 0,
	PRIMARY KEY		(module_name, view_name, field_name)
);

/* Set foreign keys */
ALTER TABLE core_models ADD FOREIGN KEY (module_name) REFERENCES core_modules(module_name);
ALTER TABLE core_models_fields ADD FOREIGN KEY (module_name) REFERENCES core_modules(module_name);
ALTER TABLE core_models_fields ADD FOREIGN KEY (table_name) REFERENCES core_models(table_name);
ALTER TABLE core_views ADD FOREIGN KEY (module_name) REFERENCES core_modules(module_name);
ALTER TABLE core_views_fields ADD FOREIGN KEY (module_name) REFERENCES core_modules(module_name);
ALTER TABLE core_views ADD FOREIGN KEY (view_name) REFERENCES core_views(view_name);

/* Insert intallation data */

INSERT INTO core_modules VALUES('modules', 'Modules', 'A module to manage all other modules', 1, 1);
INSERT INTO core_modules VALUES('config', 'Config', 'A module to manage the system configuration', 1, 1);