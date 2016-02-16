/* Create core database tables */

CREATE TABLE core_modules (
    name        VARCHAR(200) NOT NULL,
    title       VARCHAR(200),
    icon        VARCHAR(200),
    description TEXT,
    version     INT NOT NULL,
    enabled     BOOL NOT NULL DEFAULT 1,
    min_permission  INT NOT NUlL DEFAULT 0,
    system      BOOL NOT NULL DEFAULT 0,
    view_order  INT NOT NULL DEFAULT 0,
    PRIMARY KEY (name)
);

CREATE TABLE core_models (
    module_name VARCHAR(200) NOT NULL,
    name        VARCHAR(200) NOT NULL,
    PRIMARY KEY (module_name, name)
);

CREATE TABLE core_models_fields (
    module_name         VARCHAR(200) NOT NULL,
    model_name          VARCHAR(200) NOT NULL,
    name                VARCHAR(200) NOT NULL,
    type                CHAR(20) NOT NULL,
    required            BOOL NOT NULL DEFAULT 0,
    generated           BOOL NOT NULL DEFAULT 0,
    creator_module_name VARCHAR(200),
    PRIMARY KEY (module_name, model_name, name)
);

CREATE TABLE core_views (
    module_name         VARCHAR(200) NOT NULL,
    name                VARCHAR(200) NOT NULL,
    title               VARCHAR(200),
    icon                VARCHAR(200),
    type                VARCHAR(200) NOT NULL,
    datasource          VARCHAR(200),
    header_button_text  VARCHAR(200),
    header_button_icon  VARCHAR(200),
    header_button_target VARCHAR(200),
    load_data           BOOL DEFAULT 1,
    container           VARCHAR(200),
    in_sidebar          BOOL DEFAULT 0,
    does_edit           BOOL DEFAULT 0,
    show_title          BOOL DEFAULT 1,
    PRIMARY KEY (module_name, name)
);

CREATE TABLE core_views_fields (
    module_name         VARCHAR(200) NOT NULL,
    view_name           VARCHAR(200) NOT NULL,
    name                VARCHAR(200) NOT NULL,
    data_key            VARCHAR(200),
    title               VARCHAR(200),
    placeholder         VARCHAR(1000),
    type                VARCHAR(200),
    target              VARCHAR(200),
    icon                VARCHAR(200),
    enabled             BOOL NOT NULL DEFAULT 1,
    visible             BOOL NOT NULL DEFAULT 1,
    required            BOOL NOT NULL DEFAULT 0,
    view_order          INT NOT NULL DEFAULT 0,
    store_module        VARCHAR(200),
    store_name          VARCHAR(200),
    creator_module_name VARCHAR(200),
    PRIMARY KEY (module_name, view_name, name)
);

CREATE TABLE core_stores (
    name            VARCHAR(200) NOT NULL,
    module_name     VARCHAR(200) NOT NULL,
    model_name      VARCHAR(200) NOT NULL,
    data_key        VARCHAR(200) NOT NULL,
    value           VARCHAR(200) NOT NULL,
    PRIMARY KEY (module_name, name)
);

CREATE TABLE core_users (
    username        VARCHAR(200) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    name            VARCHAR(200) NOT NULL,
    default_permission  INT NOT NULL DEFAULT 0,
    token       char(64),
    PRIMARY KEY (username)
);

CREATE TABLE core_users_permissions(
    username    VARCHAR(200) NOT NULL,
    module_name VARCHAR(200) NOT NULL,
    permission  INT NOT NULL DEFAULT 0,
    PRIMARY KEY (username, module_name)
);

/* Set foreign keys */
ALTER TABLE core_models ADD FOREIGN KEY (module_name) REFERENCES core_modules(name) ON UPDATE CASCADE;
ALTER TABLE core_models_fields ADD FOREIGN KEY (module_name, model_name) REFERENCES core_models(module_name, name) ON UPDATE CASCADE;
ALTER TABLE core_models_fields ADD FOREIGN KEY (creator_module_name) REFERENCES core_modules(name) ON UPDATE CASCADE;
ALTER TABLE core_views ADD FOREIGN KEY (module_name) REFERENCES core_modules(name) ON UPDATE CASCADE;
ALTER TABLE core_views_fields ADD FOREIGN KEY (module_name, view_name) REFERENCES core_views(module_name, name) ON UPDATE CASCADE;
ALTER TABLE core_views_fields ADD FOREIGN KEY (creator_module_name) REFERENCES core_modules(name) ON UPDATE CASCADE;
ALTER TABLE core_stores ADD FOREIGN KEY (module_name) REFERENCES core_modules(name) ON UPDATE CASCADE;
ALTER TABLE core_users_permissions ADD FOREIGN KEY (username) REFERENCES core_users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE core_users_permissions ADD FOREIGN KEY (module_name) REFERENCES core_modules(name) ON UPDATE CASCADE ON DELETE CASCADE;

/* Create procedures and functions */
CREATE PROCEDURE proc_setUserModulePermission (
    in_user     VARCHAR(200),
    in_module   VARCHAR(200),
    in_perm     INT)
BEGIN
    DECLARE tmp_min_permission INT;

    SELECT min_permission
    FROM core_modules
    WHERE module_name = in_module
    INTO tmp_min_permission;

    UPDATE core_users_permissions
    SET permission = GREATEST(in_perm, tmp_min_permission)
    WHERE username = in_user
    AND module_name = in_modue;
END;

CREATE PROCEDURE proc_createPermissionsForModule (
    in_module   VARCHAR(200)
)
BEGIN
    DECLARE tmp_min_permission INT;

    SELECT min_permission
    FROM core_modules
    WHERE name = in_module
    INTO tmp_min_permission;

    INSERT INTO core_users_permissions
    SELECT username, in_module, GREATEST(default_permission, tmp_min_permission)
    FROM core_users;
END;

CREATE PROCEDURE proc_createUser (
    in_username     VARCHAR(200),
    in_password     VARCHAR(255),
    in_name         VARCHAR(200),
    in_default_perm INT
)
BEGIN
    DECLARE tmp_module_name VARCHAR(200);
    DECLARE tmp_min_permission INT;
    DECLARE tmp_finished INT;
    DECLARE cur_users CURSOR FOR SELECT name, min_permission FROM core_modules;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET tmp_finished = 1;

    INSERT INTO core_users VALUES(in_username, in_password, in_name, in_default_perm, NULL);

    OPEN cur_users;
    loop_users: LOOP
        FETCH cur_users INTO tmp_module_name, tmp_min_permission;
        IF tmp_finished = 1 THEN 
            LEAVE loop_users;
        END IF;

        INSERT INTO core_users_permissions
        VALUES (in_username, tmp_module_name, GREATEST(in_default_perm, tmp_min_permission));
    END LOOP loop_users;
END;

/* Insert intallation data */
INSERT INTO core_modules VALUES('modules', 'Modules', NULL, 'A module to manage all other modules', 1, 1, 1, 1, 0);
INSERT INTO core_modules VALUES('auth', 'Authentication', NULL, 'A module to handle authentication', 1, 1, 2, 1, 0);

/* Create admin user */
CALL proc_createUser('admin', '$2y$10$GvGoYPzIJhhvj4rRy1AgG./zUL.WtYySOFvIStFw8BRfaeOFzDWem', 'Administrator', 2);

/* Create core stores */
INSERT INTO core_stores VALUES('exportable', 'modules', 'exportable', 'id', 'name');