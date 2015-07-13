/* Create core database tables */

CREATE TABLE core_modules (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    short_name  VARCHAR(100),
    long_name   VARCHAR(100),
    description TEXT,
    version		INT,
    enabled     BOOL
);

INSERT INTO core_modules VALUES(1, 'modules', 'Modules', 'A module to manage all other modules', 1, 1);