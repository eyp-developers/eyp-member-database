/* People */

INSERT INTO people_people
SELECT
	id,
	first_name,
	last_name,
	CONCAT(first_name, ' ', last_name),
	IF(gender = 'male', 1, IF(gender = 'female', 2, NULL)),
	IF(teacher = 1, 80, IF(alumni = 1, 10, IF(is_contactperson, 20, 20))),
	email,
	!block_newsletter,
	phone,
	birthdate,
	street,
	zip,
	city,
	NULL,
	photo,
	comments,
	NULL
FROM people;

/* Payments */

INSERT INTO payments_payments
SELECT
	id,
	20,
	1,
	person_id,
	NULL,
	valid_from,
	valid_until,
	NULL
FROM payments;

/* Sessions */

INSERT INTO sessions_sessions
SELECT
	id,
	short_name,
	start_date,
	NULL,
	first_head_organizer_id,
	second_head_organizer_id,
	long_name
FROM eypsessions;

INSERT INTO sessions_participations
SELECT
	id,
	person_id,
	role_id,
	eypsession_id
FROM participations;

/* Teams */

INSERT INTO teams_teams
SELECT
	id,
	name,
	NULL,
	leader_id
FROM teams;

INSERT INTO teams_memberships
SELECT
	id,
	person_id,
	team_id
FROM memberships;
