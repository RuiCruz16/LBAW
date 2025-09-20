DROP SCHEMA IF EXISTS lbaw24135 CASCADE;

CREATE SCHEMA IF NOT EXISTS lbaw24135;
SET search_path TO lbaw24135;

-- Domains

CREATE TYPE UserActivity AS ENUM ('Active', 'Inactive', 'Blocked', 'Banned');
CREATE TYPE ProjectStatus AS ENUM ('Active', 'Completed', 'Archived', 'OnHold');
CREATE TYPE TaskPriority AS ENUM ('Low', 'Medium', 'High');
CREATE TYPE RoleTypes AS ENUM ('ProjectCoordinator', 'ProjectMember');
CREATE TYPE TaskNotificationType AS ENUM ('DeadlineChanged', 'TaskPriorityChanged', 'NewAssignment', 'TaskCompleted', 'TaskComment');

-- Tables

CREATE TABLE "User"(
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    biography TEXT,
    user_status UserActivity DEFAULT 'Active',
    last_login TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE TaskStatus (
	task_status_id SERIAL PRIMARY KEY,
    task_status_name VARCHAR(32) 
);

CREATE TABLE Project(
    project_id SERIAL PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL,
    project_description TEXT,
    created_at TIMESTAMP NOT NULL,
    project_status ProjectStatus DEFAULT 'Active',
    creator_id INT NOT NULL,
    FOREIGN KEY (creator_id) REFERENCES "User"(user_id)
);

CREATE TABLE Invitation(
    invitation_id SERIAL PRIMARY KEY,
    invitation_message TEXT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    project_id INT NOT NULL,
    FOREIGN KEY (sender_id) REFERENCES "User"(user_id),
    FOREIGN KEY (receiver_id) REFERENCES "User"(user_id),
    FOREIGN KEY (project_id) REFERENCES Project(project_id)
);

CREATE TABLE Task(
    task_id SERIAL PRIMARY KEY,
    task_title VARCHAR(255) NOT NULL,
    task_description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL,
    deadline TIMESTAMP,
    task_status_id INT,
    task_priority TaskPriority,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (task_status_id) REFERENCES TaskStatus(task_status_id),
    FOREIGN KEY (project_id) REFERENCES Project(project_id),
    FOREIGN KEY (user_id) REFERENCES "User"(user_id)
);

CREATE TABLE AssignedTask(
    user_id INT,
    task_id INT,
    PRIMARY KEY (user_id, task_id),
    FOREIGN KEY (user_id) REFERENCES "User"(user_id),
    FOREIGN KEY (task_id) REFERENCES Task(task_id)
);

CREATE TABLE "Comment"(
    comment_id SERIAL PRIMARY KEY,
    comment_content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL,
    task_id INT,
    FOREIGN KEY (task_id) REFERENCES Task(task_id)
);

CREATE TABLE CommentRelation(
    child_id INT PRIMARY KEY,
    parent_id INT,
    FOREIGN KEY (child_id) REFERENCES "Comment"(comment_id),
    FOREIGN KEY (parent_id) REFERENCES "Comment"(comment_id)
);

CREATE TABLE ProjectRole(
    user_id INT,
    project_id INT,
    user_role RoleTypes DEFAULT 'ProjectMember',
    PRIMARY KEY (user_id, project_id),
    FOREIGN KEY (user_id) REFERENCES "User"(user_id),
    FOREIGN KEY (project_id) REFERENCES Project(project_id)
);

CREATE TABLE FavouriteProject(
    user_id INT,
    project_id INT,
    PRIMARY KEY (user_id, project_id),
    FOREIGN KEY (user_id) REFERENCES "User"(user_id),
    FOREIGN KEY (project_id) REFERENCES Project(project_id)
);

CREATE TABLE RoleChangeNotification(
    role_change_notification_id SERIAL PRIMARY KEY,
    created_at TIMESTAMP NOT NULL,
    updated_user INT NOT NULL,
    updated_project INT NOT NULL,
    updated_role RoleTypes,
    FOREIGN KEY (updated_user, updated_project) REFERENCES ProjectRole(user_id, project_id)
);

CREATE TABLE InviteNotification(
    invite_notification_id SERIAL PRIMARY KEY,
    created_at TIMESTAMP NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES "User"(user_id)
);

CREATE TABLE TaskNotification(
    task_notification_id SERIAL PRIMARY KEY,
    created_at TIMESTAMP NOT NULL,
    user_id INT NOT NULL,
    task_id INT NOT NULL,
    notify_type TaskNotificationType NOT NULL,
    FOREIGN KEY (user_id) REFERENCES "User"(user_id)
    FOREIGN KEY (task_id) REFERENCES Task(task_id)
);

CREATE TABLE Image(
    image_id SERIAL PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    user_id INT UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES "User"(user_id)
);

CREATE TABLE ProjectTaskStatus(
    task_status_id INT NOT NULL,
    project_id INT NOT NULL,
    PRIMARY KEY (task_status_id, project_id),
    FOREIGN KEY (task_status_id) REFERENCES TaskStatus(task_status_id),
    FOREIGN KEY (project_id) REFERENCES Project(project_id)
);

-- Indexes

CREATE INDEX idx_task_deadline ON Task USING btree (deadline);
CLUSTER Task USING idx_task_deadline;

CREATE INDEX idx_task_status ON Task USING btree (task_status_id);

CREATE INDEX idx_project_status ON Project USING btree (project_status);
CLUSTER Project USING idx_project_status;

ALTER TABLE Task
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION task_search_update() RETURNS TRIGGER AS $$
BEGIN
 IF TG_OP = 'INSERT' THEN
        NEW.tsvectors = (
         setweight(to_tsvector('english', NEW.task_title), 'A') ||
         setweight(to_tsvector('english', NEW.task_description), 'B')
        );
 END IF;
 IF TG_OP = 'UPDATE' THEN
         IF (NEW.task_title <> OLD.task_title OR NEW.task_description <> OLD.task_description) THEN
           NEW.tsvectors = (
             setweight(to_tsvector('english', NEW.task_title), 'A') ||
             setweight(to_tsvector('english', NEW.task_description), 'B')
           );
         END IF;
 END IF;
 RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER task_search_update
 BEFORE INSERT OR UPDATE ON Task
 FOR EACH ROW
 EXECUTE PROCEDURE task_search_update();

CREATE INDEX task_search_idx ON Task USING GIN (tsvectors);

ALTER TABLE Project
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION project_search_update() RETURNS TRIGGER AS $$
BEGIN
 IF TG_OP = 'INSERT' THEN
        NEW.tsvectors = (
         setweight(to_tsvector('english', NEW.project_name), 'A') ||
         setweight(to_tsvector('english', NEW.project_description), 'B')
        );
 END IF;
 IF TG_OP = 'UPDATE' THEN
         IF (NEW.project_name <> OLD.project_name OR NEW.project_description <> OLD.project_description) THEN
           NEW.tsvectors = (
             setweight(to_tsvector('english', NEW.project_name), 'A') ||
             setweight(to_tsvector('english', NEW.project_description), 'B')
           );
         END IF;
 END IF;
 RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER project_search_update
 BEFORE INSERT OR UPDATE ON Project
 FOR EACH ROW
 EXECUTE PROCEDURE project_search_update();

CREATE INDEX project_search_idx ON Project USING GIN (tsvectors);

-- Triggers

CREATE OR REPLACE FUNCTION notify_task_completion() RETURNS TRIGGER AS $$
DECLARE
    completed_status_id INT;
BEGIN
    SELECT task_status_id INTO completed_status_id
    FROM TaskStatus
    WHERE task_status_name = 'Completed';

    IF NEW.task_status_id = completed_status_id AND OLD.task_status_id IS DISTINCT FROM NEW.task_status_id THEN
        INSERT INTO TaskNotification (created_at, task_id, notify_type) 
        VALUES (NOW(), NEW.task_id, 'TaskCompleted');
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER task_completion_trigger
AFTER UPDATE OF task_status_id ON Task
FOR EACH ROW
EXECUTE FUNCTION notify_task_completion();

CREATE OR REPLACE FUNCTION notify_coordinator_change() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO RoleChangeNotification (created_at, updated_user, updated_project, updated_role)
    VALUES (NOW(), NEW.user_id, NEW.project_id, NEW.user_role);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER coordinator_change_trigger
AFTER UPDATE OF user_role ON ProjectRole
FOR EACH ROW
WHEN (NEW.user_role = 'ProjectCoordinator')
EXECUTE FUNCTION notify_coordinator_change();

CREATE OR REPLACE FUNCTION notify_task_assignment() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO TaskNotification (created_at, task_id, notify_type) 
    VALUES (NOW(), NEW.task_id, 'NewAssignment');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER task_assignment_trigger
AFTER INSERT ON AssignedTask
FOR EACH ROW
EXECUTE FUNCTION notify_task_assignment();

CREATE OR REPLACE FUNCTION notify_invitation_creation() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO InviteNotification (created_at, user_id) 
    VALUES (NOW(), NEW.receiver_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER invitation_creation_trigger
AFTER INSERT ON Invitation
FOR EACH ROW
EXECUTE FUNCTION notify_invitation_creation();

CREATE OR REPLACE FUNCTION notify_task_comment() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO TaskNotification (created_at, task_id, notify_type) 
    VALUES (NOW(), NEW.task_id, 'TaskComment');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER task_comment_trigger
AFTER INSERT ON "Comment"
FOR EACH ROW
EXECUTE FUNCTION notify_task_comment();

CREATE OR REPLACE FUNCTION notify_deadline_change() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO TaskNotification (created_at, task_id, notify_type) 
    VALUES (NOW(), NEW.task_id, 'DeadlineChanged');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER deadline_change_trigger
AFTER UPDATE OF deadline ON Task
FOR EACH ROW
WHEN (OLD.deadline IS DISTINCT FROM NEW.deadline)
EXECUTE FUNCTION notify_deadline_change();
