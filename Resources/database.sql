/* Create core database tables */

CREATE TABLE core_modules (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    short_name  VARCHAR(100),
    long_name   VARCHAR(100),
    description TEXT,
    enabled     BOOL
);