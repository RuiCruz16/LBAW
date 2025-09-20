--
-- Use a specific schema and set it as default - thingy.
--
DROP SCHEMA IF EXISTS planora CASCADE;
CREATE SCHEMA IF NOT EXISTS planora;
SET search_path TO planora;

--
-- Drop any existing tables.
--
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS cards CASCADE;
DROP TABLE IF EXISTS items CASCADE;

--
-- Create tables.
--
CREATE TYPE UserActivity AS ENUM ('Active', 'Inactive', 'Blocked', 'Banned');
CREATE TYPE ProjectStatus AS ENUM ('Active', 'Completed', 'Archived', 'OnHold');
CREATE TYPE TaskPriority AS ENUM ('Low', 'Medium', 'High');
CREATE TYPE RoleTypes AS ENUM ('ProjectCoordinator', 'ProjectMember', 'ProjectCreator');
CREATE TYPE TaskNotificationType AS ENUM ('DeadlineChanged', 'TaskPriorityChanged', 'NewAssignment', 'TaskCompleted', 'TaskComment');


CREATE TABLE users (
                       id SERIAL PRIMARY KEY,
                       username VARCHAR(255) UNIQUE NOT NULL,
                       password VARCHAR NOT NULL,
                       email VARCHAR(255) UNIQUE NOT NULL,
                       biography TEXT,
                       user_status UserActivity DEFAULT 'Active',
                       last_login TIMESTAMP NOT NULL,
                       created_at TIMESTAMP NOT NULL,
                       is_admin BOOLEAN DEFAULT FALSE,
                       remember_token VARCHAR
);

CREATE TABLE project (
                         id SERIAL PRIMARY KEY,
                         project_name VARCHAR(255) NOT NULL,
                         project_description TEXT,
                         created_at TIMESTAMP NOT NULL,
                         project_status ProjectStatus DEFAULT 'Active',
                         creator_id INT NOT NULL
);

CREATE TABLE task_status (
                             id SERIAL PRIMARY KEY,
                             task_status_name VARCHAR(32)
);

CREATE TABLE task (
                      id SERIAL PRIMARY KEY,
                      task_title VARCHAR(255) NOT NULL,
                      task_description TEXT NOT NULL,
                      created_at TIMESTAMP NOT NULL,
                      deadline TIMESTAMP,
                      task_status_id INT,
                      task_priority TaskPriority,
                      project_id INT NOT NULL,
                      FOREIGN KEY (task_status_id) REFERENCES task_status(id),
                      FOREIGN KEY (project_id) REFERENCES project(id)
);

CREATE TABLE project_role(
                             user_id INT,
                             project_id INT,
                             user_role RoleTypes DEFAULT 'ProjectMember',
                             PRIMARY KEY (user_id, project_id),
                             FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                             FOREIGN KEY (project_id) REFERENCES project(id)
);

CREATE TABLE images(
                       id SERIAL PRIMARY KEY,
                       image_path VARCHAR(255) NOT NULL,
                       user_id INT UNIQUE NOT NULL,
                       FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE comments(
                          id SERIAL PRIMARY KEY,
                          comment_content TEXT NOT NULL,
                          created_at TIMESTAMP NOT NULL,
                          task_id INT NOT NULL,
                          user_id INT,
                          FOREIGN KEY (user_id) REFERENCES users(id),
                          FOREIGN KEY (task_id) REFERENCES task(id)
);

CREATE TABLE assigned_task (
    id SERIAL PRIMARY KEY,
    user_id INT,
    task_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (task_id) REFERENCES task(id)
);

CREATE TABLE project_invitation (
  id SERIAL PRIMARY KEY,
  invitation_message TEXT,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  sent_at TIMESTAMP NOT NULL,
  project_id INT NOT NULL,
  response BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (receiver_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES project(id)
);

CREATE TABLE password_resets (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL
);

CREATE TABLE project_mail_invitation (
    email VARCHAR(255) NOT NULL,
    project_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (email, project_id),
    FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE
);

CREATE TABLE favorite_project(
    user_id INT,
    project_id INT,
    PRIMARY KEY (user_id, project_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES project(id)
);

CREATE TABLE change_role_notifications (
  id SERIAL PRIMARY KEY,
  change_role_message TEXT,
  project_id INT NOT NULL,
  sender_id INT NOT NULL,
  user_role_changed_id INT NOT NULL,
  receiver_id INT NOT NULL,
  sent_at TIMESTAMP NOT NULL,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (user_role_changed_id) REFERENCES users(id),
  FOREIGN KEY (receiver_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES project(id)
);

CREATE TABLE task_notifications(
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP NOT NULL,
    user_id INT NOT NULL,
    task_id INT NOT NULL,
    notify_type TaskNotificationType NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (task_id) REFERENCES task(id)
);

CREATE INDEX idx_task_deadline ON task USING btree (deadline);
CLUSTER Task USING idx_task_deadline;

CREATE INDEX idx_task_status ON task USING btree (task_status_id);

CREATE INDEX idx_project_status ON project USING btree (project_status);
CLUSTER Project USING idx_project_status;

ALTER TABLE task
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
    BEFORE INSERT OR UPDATE ON task
                         FOR EACH ROW
                         EXECUTE PROCEDURE task_search_update();

CREATE INDEX task_search_idx ON task USING GIN (tsvectors);

ALTER TABLE project
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
    BEFORE INSERT OR UPDATE ON project
                         FOR EACH ROW
                         EXECUTE PROCEDURE project_search_update();

CREATE INDEX project_search_idx ON project USING GIN (tsvectors);

INSERT INTO users VALUES (DEFAULT,'anonymous','$2y$10$abcdefghijkLmnopqrstuvwxyzExampleHash123456','anonymous@system.local','This account has been deleted.','Active',NOW(),NOW(),FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202205295', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202205295@up.pt', 'Diogo is a computer engineering student passionate about learning new technologies. He loves solving problems and collaborating on interesting projects.', 'Active', NOW(), NOW(), TRUE);
INSERT INTO users VALUES (DEFAULT, 'up202206205', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202206205@up.pt', 'Gonçalo is passionate about programming and interface design. He enjoys exploring new tools and sharing his knowledge with the community.', 'Active', NOW(), NOW(), FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208296', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208296@up.pt', 'Lucas is a full-stack developer with a passion for building scalable web applications. He enjoys working on both the front-end and back-end to create seamless user experiences.', 'Inactive', NOW() - INTERVAL '30 days', NOW() - INTERVAL '60 days', FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208011', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208011@up.pt', 'Rui is a senior software engineer who specializes in data engineering and machine learning. He enjoys working with large datasets and building intelligent systems.', 'Active', NOW(), NOW(), FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208297', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208297@up.pt', 'Ricardo is a product manager with experience leading cross-functional teams. They focus on delivering user-centered products and iterating based on feedback.', 'Active', NOW(), NOW(), FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208298', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208298@up.pt', 'Nuno is a digital marketing strategist with a focus on SEO and content creation. They help businesses grow their online presence through effective marketing campaigns.', 'Inactive', NOW() - INTERVAL '10 days', NOW() - INTERVAL '20 days', FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208299', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208299@up.pt', 'Miguel is a graphic designer who specializes in branding and UI/UX design. They are passionate about creating visually appealing and user-friendly digital experiences.', 'Active', NOW(), NOW(), FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208300', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208300@up.pt', 'Adalberto is a DevOps engineer who focuses on automating infrastructure and improving the deployment pipeline for efficient software delivery.', 'Active', NOW(), NOW(), FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208301', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208301@up.pt', 'Serafim is a software architect who designs scalable and maintainable software systems. They are highly experienced in cloud computing and microservices architecture.', 'Active', NOW(), NOW(), FALSE);
INSERT INTO users VALUES (DEFAULT, 'up202208302', '$2y$10$OVv/xxiV6GiKLFBSAXX.ROmJTMtcd/9NRenzIxbvPkr3SNMT9CXZq', 'up202208302@up.pt', 'Rodrigo is a system administrator with a passion for optimizing IT infrastructure and ensuring system security. They have extensive experience in Linux server management.', 'Inactive', NOW() - INTERVAL '5 days', NOW() - INTERVAL '15 days', FALSE);

INSERT INTO task_status (task_status_name) VALUES
                                               ('To Do'),
                                               ('In Progress'),
                                               ('Completed');

-- Projects for Diogo (User 1)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Task Tracker', 'A tool for managing and tracking daily tasks and activities', NOW(), 'Active', 3),
                                                                                                    ('Code Collaboration Platform', 'A platform to facilitate collaborative coding and code review', NOW(), 'Active', 3),
                                                                                                    ('Product Roadmap Tool', 'A platform for managing product development roadmaps', NOW() - INTERVAL '5 days', 'Active', 3),
                                                                                                    ('Product Analytics Dashboard', 'A dashboard for tracking product usage and performance metrics', NOW() - INTERVAL '15 days', 'Archived', 3),
                                                                                                    ('Online Portfolio Builder', 'A tool to create personalized online portfolios', NOW() - INTERVAL '5 days', 'Active', 3),
                                                                                                    ('API Gateway System', 'A platform for managing and routing API requests to different services', NOW() - INTERVAL '10 days', 'Active', 3),
                                                                                                    ('Automated Testing Suite', 'A suite of tools to automate the testing of web applications', NOW() - INTERVAL '20 days', 'Active', 3);

-- Projects for Gonçalo (User 2)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Web Design Tool', 'A tool to help designers create and prototype websites', NOW(), 'Active', 3),
                                                                                                    ('User Feedback System', 'A system to collect and analyze user feedback for digital products', NOW() - INTERVAL '10 days', 'Active', 3),
                                                                                                    ('SEO Optimization Tool', 'A tool to help businesses optimize their websites for search engines', NOW() - INTERVAL '10 days', 'Active', 3),
                                                                                                    ('Server Monitoring System', 'A platform for monitoring server performance and uptime', NOW(), 'Active', 3),
                                                                                                    ('Network Security System', 'A system to monitor and protect network security in real-time', NOW() - INTERVAL '5 days', 'Active', 3),
                                                                                                    ('Online Storefront', 'A platform to help businesses set up online stores', NOW() - INTERVAL '15 days', 'Completed', 3),
                                                                                                    ('Digital Marketing Analytics', 'A platform to track and analyze digital marketing campaigns', NOW() - INTERVAL '25 days', 'Active', 3),
                                                                                                    ('UX/UI Design System', 'A system for managing reusable design components for web applications', NOW() - INTERVAL '30 days', 'Active', 3);

-- Projects for Lucas (User 3)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Real-Time Chat Application', 'A messaging platform with real-time chat capabilities', NOW(), 'Active', 3),
                                                                                                    ('Event Notification System', 'A system that sends event notifications to users based on their preferences', NOW() - INTERVAL '10 days', 'Active', 3),
                                                                                                    ('UI/UX Design System', 'A system for managing and reusing design components for UI/UX design', NOW() - INTERVAL '5 days', 'Active', 3),
                                                                                                    ('Content Management System for Blogs', 'A CMS for creating and managing blogs and articles', NOW() - INTERVAL '20 days', 'Active', 3),
                                                                                                    ('Video Streaming Platform', 'A platform to upload and stream videos online', NOW(), 'Active', 3),
                                                                                                    ('Collaborative Whiteboard', 'A platform for users to draw and brainstorm ideas in real time', NOW() - INTERVAL '15 days', 'Active', 3),
                                                                                                    ('Interactive Course Platform', 'A platform for creating and hosting interactive online courses', NOW() - INTERVAL '25 days', 'Archived', 3);

-- Projects for Rui (User 4)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('AI-Powered Analytics Tool', 'An analytics platform powered by artificial intelligence for predictive insights', NOW(), 'Active', 4),
                                                                                                    ('Big Data Platform', 'A platform to process and analyze large datasets in real time', NOW() - INTERVAL '5 days', 'Active', 4),
                                                                                                    ('CI/CD Pipeline Setup', 'A solution for setting up continuous integration and continuous delivery pipelines', NOW() - INTERVAL '10 days', 'Active', 4),
                                                                                                    ('Microservices Architecture', 'A platform for designing and deploying microservices-based systems', NOW(), 'Active', 4),
                                                                                                    ('Cloud Service Management', 'A tool for managing cloud services and resources', NOW() - INTERVAL '10 days', 'Active', 4),
                                                                                                    ('Data Visualization Tool', 'A tool for visualizing large datasets in various formats', NOW() - INTERVAL '10 days', 'Completed', 4),
                                                                                                    ('Predictive Modeling System', 'A tool for building and deploying predictive models on large datasets', NOW(), 'Active', 4),
                                                                                                    ('Data Warehousing Solution', 'A platform for managing and querying large-scale data warehouses', NOW() - INTERVAL '15 days', 'Active', 4);

-- Projects for Ricardo (User 5)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Customer Feedback Platform', 'A system for collecting and analyzing customer feedback', NOW(), 'Active', 5),
                                                                                                    ('Product Launch Tracker', 'A tool for tracking product launches and performance', NOW() - INTERVAL '5 days', 'Active', 5);

-- Projects for Nuno (User 6)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Social Media Scheduler', 'A tool for scheduling and managing social media posts across platforms', NOW(), 'Active', 6),
                                                                                                    ('Email Campaign Manager', 'A platform for creating and managing email marketing campaigns', NOW() - INTERVAL '20 days', 'Completed', 6),
                                                                                                    ('SEO Performance Tracker', 'A tool to track SEO metrics and performance for websites', NOW() - INTERVAL '10 days', 'Active', 6);

-- Projects for Miguel (User 7)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Brand Identity Platform', 'A platform to help businesses create and manage their brand identity', NOW(), 'Active', 7),
                                                                                                    ('Interactive Web Design', 'A tool to create interactive and engaging web designs', NOW() - INTERVAL '15 days', 'Archived', 7),
                                                                                                    ('UI Design Kit', 'A toolkit for designers to streamline UI development', NOW(), 'Active', 7);

-- Projects for Adalberto (User 8)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Cloud Infrastructure Automation', 'A platform for automating cloud infrastructure provisioning and management', NOW(), 'Active', 8),
                                                                                                    ('DevOps Dashboard', 'A dashboard for monitoring DevOps metrics and performance', NOW() - INTERVAL '20 days', 'Completed', 8),
                                                                                                    ('Continuous Integration Platform', 'A tool to automate code integration and deployment', NOW() - INTERVAL '5 days', 'Active', 8);

-- Projects for Serafim (User 9)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('Distributed Systems Framework', 'A framework for building and managing distributed systems', NOW() - INTERVAL '25 days', 'Archived', 9),
                                                                                                    ('Cloud Native Architecture', 'A platform for developing and deploying cloud-native applications', NOW(), 'Active', 9);

-- Projects for Rodrigo (User 10)
INSERT INTO project (project_name, project_description, created_at, project_status, creator_id) VALUES
                                                                                                    ('IT Infrastructure Optimization', 'A platform to optimize and manage IT infrastructure', NOW() - INTERVAL '15 days', 'Completed', 10),
                                                                                                    ('Server Monitoring Dashboard', 'A tool to monitor and report on server health and performance', NOW(), 'Active', 10);

-- Tasks for Diogo (User 1)

-- Task Tracker
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Task Management System', 'Develop the core task management system to add, update, and delete tasks.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 1),
                                                                                                                     ('Task Prioritization', 'Implement task prioritization features for deadlines and urgency.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 1),
                                                                                                                     ('User Authentication', 'Set up user authentication for secure login and management.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 1),
                                                                                                                     ('Task Notifications', 'Implement notifications to alert users of task updates.', NOW(), NOW() + INTERVAL '12 days', 3, 'Medium', 1),
                                                                                                                     ('UI Design for Dashboard', 'Design the user interface for the task dashboard.', NOW(), NOW() + INTERVAL '8 days', 2, 'High', 1);

-- Code Collaboration Platform
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Real-Time Collaboration', 'Implement real-time collaboration on code editing.', NOW(), NOW() + INTERVAL '14 days', 1, 'High', 2),
                                                                                                                     ('Code Review System', 'Develop a system to submit, track, and approve code reviews.', NOW(), NOW() + INTERVAL '15 days', 2, 'Medium', 2),
                                                                                                                     ('User Roles and Permissions', 'Define user roles such as Admin, Reviewer, Contributor.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 2),
                                                                                                                     ('Version Control Integration', 'Integrate with Git for version control in the platform.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 2),
                                                                                                                     ('Push Notifications', 'Add push notifications for code changes.', NOW(), NOW() + INTERVAL '12 days', 3, 'Low', 2);

-- Product Roadmap Tool
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Roadmap Templates', 'Develop templates for product roadmaps.', NOW(), NOW() + INTERVAL '5 days', 1, 'Medium', 3),
                                                                                                                     ('Team Collaboration Features', 'Add features for team collaboration and commenting on roadmaps.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 3),
                                                                                                                     ('Integration with Jira', 'Integrate with Jira to sync roadmap tasks and issues.', NOW(), NOW() + INTERVAL '14 days', 1, 'Low', 3),
                                                                                                                     ('Timeline View', 'Develop a timeline view for product roadmap visualization.', NOW(), NOW() + INTERVAL '7 days', 3, 'High', 3),
                                                                                                                     ('User Feedback', 'Implement a feedback system for product roadmaps.', NOW(), NOW() + INTERVAL '8 days', 2, 'Medium', 3);

-- Product Analytics Dashboard
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Data Sources Integration', 'Integrate data sources for analytics (e.g., Google Analytics, custom APIs).', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 4),
                                                                                                                     ('Build Dashboard Interface', 'Develop a user-friendly interface for displaying product analytics.', NOW(), NOW() + INTERVAL '14 days', 2, 'High', 4),
                                                                                                                     ('Real-Time Data Processing', 'Implement real-time processing for live data analytics.', NOW(), NOW() + INTERVAL '15 days', 3, 'Medium', 4),
                                                                                                                     ('Reports Generation', 'Develop automated reports for product usage statistics.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 4),
                                                                                                                     ('Data Visualization', 'Create visualizations for various metrics on the dashboard.', NOW(), NOW() + INTERVAL '12 days', 3, 'Low', 4);

-- Online Portfolio Builder
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Portfolio Templates', 'Design and implement portfolio templates for users.', NOW(), NOW() + INTERVAL '5 days', 2, 'High', 5),
                                                                                                                     ('User Registration', 'Set up user registration and profile management.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 5),
                                                                                                                     ('Build Portfolio Editor', 'Develop the editor for users to customize their portfolios.', NOW(), NOW() + INTERVAL '15 days', 1, 'Medium', 5),
                                                                                                                     ('Publish Functionality', 'Add a publish feature to make portfolios public.', NOW(), NOW() + INTERVAL '20 days', 2, 'Low', 5),
                                                                                                                     ('SEO Optimization', 'Optimize portfolio pages for search engines.', NOW(), NOW() + INTERVAL '10 days', 3, 'Medium', 5);

-- API Gateway System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design API Gateway Architecture', 'Design the architecture for the API Gateway.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 6),
                                                                                                                     ('Implement Rate Limiting', 'Add rate limiting to APIs for better control.', NOW(), NOW() + INTERVAL '7 days', 2, 'Medium', 6),
                                                                                                                     ('Security Features', 'Implement authentication and authorization for APIs.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 6),
                                                                                                                     ('Logging and Monitoring', 'Set up logging and monitoring for API usage.', NOW(), NOW() + INTERVAL '15 days', 2, 'Low', 6),
                                                                                                                     ('API Documentation', 'Create detailed API documentation for developers.', NOW(), NOW() + INTERVAL '20 days', 3, 'Low', 6);

-- Automated Testing Suite
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Test Cases', 'Develop test cases for different use cases and edge cases.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 7),
                                                                                                                     ('Set Up CI Integration', 'Integrate automated testing with the continuous integration pipeline.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 7),
                                                                                                                     ('Generate Test Reports', 'Create a system for generating and viewing test results and logs.', NOW(), NOW() + INTERVAL '12 days', 3, 'Medium', 7),
                                                                                                                     ('Add Mock Services', 'Implement mock services to simulate third-party API calls during testing.', NOW(), NOW() + INTERVAL '15 days', 2, 'Low', 7),
                                                                                                                     ('Test Execution Dashboard', 'Build a dashboard to view and track the status of all tests.', NOW(), NOW() + INTERVAL '20 days', 2, 'Low', 7);

-- Tasks for Gonçalo (User 2)

-- Web Design Tool
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design User Interface', 'Create the main user interface for the web design tool.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 8),
                                                                                                                     ('Implement Drag-and-Drop Feature', 'Develop a drag-and-drop interface for the design canvas.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 8),
                                                                                                                     ('Add Export Functionality', 'Enable users to export designs as images or HTML code.', NOW(), NOW() + INTERVAL '14 days', 3, 'Medium', 8),
                                                                                                                     ('Integrate Design Templates', 'Add a library of templates that users can customize.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 8),
                                                                                                                     ('User Feedback Feature', 'Implement a feature to collect user feedback on designs.', NOW(), NOW() + INTERVAL '15 days', 2, 'Low', 8);

-- User Feedback System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Feedback Form', 'Create a feedback form for users to submit their opinions.', NOW(), NOW() + INTERVAL '5 days', 1, 'Medium', 9),
                                                                                                                     ('User Data Collection', 'Develop a system to collect user data for feedback analysis.', NOW(), NOW() + INTERVAL '7 days', 2, 'High', 9),
                                                                                                                     ('Build Analytics Dashboard', 'Create a dashboard to analyze the feedback data collected.', NOW(), NOW() + INTERVAL '10 days', 3, 'Medium', 9),
                                                                                                                     ('Integrate with Survey Tools', 'Integrate the feedback system with external survey tools like Google Forms.', NOW(), NOW() + INTERVAL '15 days', 1, 'Low', 9),
                                                                                                                     ('Real-Time Feedback Updates', 'Implement real-time updates for feedback submissions.', NOW(), NOW() + INTERVAL '12 days', 2, 'Low', 9);

-- SEO Optimization Tool
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Implement SEO Analysis Feature', 'Develop a tool to analyze websites for SEO performance.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 10),
                                                                                                                     ('Create Keyword Suggestions', 'Implement a system to suggest keywords for SEO optimization.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 10),
                                                                                                                     ('Build SEO Report Generator', 'Develop a system to generate detailed SEO reports for websites.', NOW(), NOW() + INTERVAL '12 days', 3, 'Medium', 10),
                                                                                                                     ('Integrate with Google Analytics', 'Integrate the tool with Google Analytics for more detailed SEO insights.', NOW(), NOW() + INTERVAL '15 days', 1, 'Low', 10),
                                                                                                                     ('SEO Tips and Recommendations', 'Provide real-time SEO tips and suggestions for website optimization.', NOW(), NOW() + INTERVAL '5 days', 2, 'Low', 10);

-- Server Monitoring System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Develop Server Monitoring Dashboard', 'Create a real-time dashboard to monitor server health and status.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 11),
                                                                                                                     ('Implement Alerts and Notifications', 'Add alerts for server downtime or performance issues.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 11),
                                                                                                                     ('Integrate Server Metrics', 'Integrate server performance metrics like CPU, RAM, and disk usage.', NOW(), NOW() + INTERVAL '7 days', 1, 'Medium', 11),
                                                                                                                     ('Log Aggregation System', 'Develop a system for aggregating and visualizing server logs.', NOW(), NOW() + INTERVAL '12 days', 2, 'Low', 11),
                                                                                                                     ('Server API Integration', 'Create API endpoints for querying server data programmatically.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 11);

-- Network Security System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Build Firewall Integration', 'Integrate network security with firewall systems.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 12),
                                                                                                                     ('Real-Time Threat Detection', 'Develop a system for detecting real-time network threats.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 12),
                                                                                                                     ('Create User Access Management', 'Implement role-based access controls and security policies.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 12),
                                                                                                                     ('Create Alerts for Suspicious Activity', 'Develop alerts for detecting suspicious network activity.', NOW(), NOW() + INTERVAL '12 days', 2, 'Low', 12),
                                                                                                                     ('Security Audit Logging', 'Set up a system for logging and auditing network activity.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 12);

-- Online Storefront
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Build Storefront UI', 'Develop the user interface for the online storefront.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 13),
                                                                                                                     ('Add Product Management Features', 'Implement features for adding, editing, and managing products.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 13),
                                                                                                                     ('Integrate Payment Gateway', 'Add payment gateway integration for online transactions.', NOW(), NOW() + INTERVAL '14 days', 3, 'High', 13),
                                                                                                                     ('Add Order Tracking System', 'Create a system to track orders and shipments for customers.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 13),
                                                                                                                     ('Customer Feedback System', 'Develop a system for customers to submit feedback on products.', NOW(), NOW() + INTERVAL '15 days', 3, 'Medium', 13);

-- Digital Marketing Analytics
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Analytics Dashboard', 'Design and develop a dashboard for digital marketing metrics.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 14),
                                                                                                                     ('Integrate with Social Media APIs', 'Integrate with social media platforms for marketing data collection.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 14),
                                                                                                                     ('Set Up Campaign Tracking', 'Implement campaign tracking for different digital marketing strategies.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 14),
                                                                                                                     ('Develop Reporting System', 'Create a reporting system to analyze digital marketing performance.', NOW(), NOW() + INTERVAL '15 days', 3, 'Medium', 14),
                                                                                                                     ('Create Marketing Insights', 'Provide insights and suggestions based on marketing campaign data.', NOW(), NOW() + INTERVAL '20 days', 2, 'Low', 14);

-- UX/UI Design System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design UI Components Library', 'Develop a reusable library of UI components for web applications.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 15),
                                                                                                                     ('Create Design System Documentation', 'Document the design system guidelines for developers and designers.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 15),
                                                                                                                     ('Build Version Control for Components', 'Implement version control to track UI component changes.', NOW(), NOW() + INTERVAL '12 days', 2, 'Low', 15),
                                                                                                                     ('Integrate with Design Tools', 'Integrate the design system with tools like Figma and Sketch.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 15),
                                                                                                                     ('User Testing of Components', 'Conduct user testing on the UI components for usability.', NOW(), NOW() + INTERVAL '20 days', 3, 'Medium', 15);

-- Tasks for Lucas (User 3)

-- Real-Time Chat Application
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Develop Chat Interface', 'Create the main chat interface for real-time communication.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 16),
                                                                                                                     ('Integrate Real-Time Messaging', 'Implement WebSocket-based real-time messaging functionality.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 16),
                                                                                                                     ('User Authentication', 'Develop user login and authentication for the chat platform.', NOW(), NOW() + INTERVAL '5 days', 2, 'High', 16),
                                                                                                                     ('Implement Message Storage', 'Set up a backend system to store and retrieve messages.', NOW(), NOW() + INTERVAL '15 days', 2, 'Medium', 16),
                                                                                                                     ('Add Emoji Support', 'Implement support for emojis in chat messages.', NOW(), NOW() + INTERVAL '12 days', 3, 'Low', 16);

-- Event Notification System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Notification System', 'Create the notification system to alert users about events.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 17),
                                                                                                                     ('User Preference Settings', 'Develop user preferences to customize notification types.', NOW(), NOW() + INTERVAL '7 days', 2, 'Medium', 17),
                                                                                                                     ('Integrate Push Notifications', 'Add support for push notifications to mobile devices.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 17),
                                                                                                                     ('Build Notification History', 'Create a history of sent notifications for users to view.', NOW(), NOW() + INTERVAL '12 days', 3, 'Low', 17),
                                                                                                                     ('Real-Time Notification Updates', 'Implement real-time notification updates for users.', NOW(), NOW() + INTERVAL '15 days', 1, 'Medium', 17);

-- UI/UX Design System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Develop Design System', 'Create a system to manage and reuse UI/UX components for applications.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 18),
                                                                                                                     ('Document Design Guidelines', 'Write detailed documentation on design system usage for developers.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 18),
                                                                                                                     ('Create Design Tokens', 'Develop design tokens for consistent design implementation across projects.', NOW(), NOW() + INTERVAL '5 days', 1, 'Medium', 18),
                                                                                                                     ('Integrate with Figma', 'Integrate the design system with design tools like Figma for easier usage.', NOW(), NOW() + INTERVAL '12 days', 3, 'Low', 18),
                                                                                                                     ('Version Control for Components', 'Set up version control for the UI components library.', NOW(), NOW() + INTERVAL '15 days', 2, 'Low', 18);

-- Content Management System for Blogs
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create Blog Post Editor', 'Develop an editor for creating and formatting blog posts.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 19),
                                                                                                                     ('Add Post Categorization', 'Implement categories for blog posts to help users organize content.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 19),
                                                                                                                     ('Develop Comment System', 'Build a comment section for each blog post.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 19),
                                                                                                                     ('Set Up User Roles', 'Implement user roles for managing blog post creation and moderation.', NOW(), NOW() + INTERVAL '15 days', 3, 'Medium', 19),
                                                                                                                     ('SEO Optimization for Posts', 'Add SEO features to optimize blog posts for search engines.', NOW(), NOW() + INTERVAL '20 days', 1, 'Low', 19);

-- Video Streaming Platform
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Develop Video Upload Feature', 'Create the functionality to upload videos to the platform.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 20),
                                                                                                                     ('Build Video Streaming Engine', 'Develop a streaming engine to serve videos to users.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 20),
                                                                                                                     ('Add Video Search Functionality', 'Implement a search system to find videos by title or tags.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 20),
                                                                                                                     ('User Subscription System', 'Create a subscription system for premium content access.', NOW(), NOW() + INTERVAL '15 days', 2, 'Medium', 20),
                                                                                                                     ('Integrate with Social Media', 'Add features to allow users to share videos on social media platforms.', NOW(), NOW() + INTERVAL '18 days', 3, 'Low', 20);

-- Collaborative Whiteboard
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Build Whiteboard Canvas', 'Develop the main drawing canvas for the collaborative whiteboard.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 21),
                                                                                                                     ('Implement Real-Time Collaboration', 'Enable multiple users to collaborate in real time on the whiteboard.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 21),
                                                                                                                     ('Add Drawing Tools', 'Create a set of drawing tools like pencil, shapes, and text.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 21),
                                                                                                                     ('Create Object Layers', 'Allow users to work on different layers within the whiteboard.', NOW(), NOW() + INTERVAL '15 days', 2, 'Low', 21),
                                                                                                                     ('Save and Export Whiteboard', 'Implement functionality to save and export whiteboard sessions.', NOW(), NOW() + INTERVAL '20 days', 3, 'Low', 21);

-- Interactive Course Platform
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Develop Course Creation Tool', 'Create the tool for building and managing courses.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 22),
                                                                                                                     ('Add Video Integration', 'Integrate video content into the courses.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 22),
                                                                                                                     ('Create Quizzes and Assignments', 'Implement quizzes and assignments within the courses.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 22),
                                                                                                                     ('Develop User Progress Tracker', 'Create a system to track user progress throughout the course.', NOW(), NOW() + INTERVAL '15 days', 3, 'Medium', 22),
                                                                                                                     ('Implement Discussion Forums', 'Add forums for students to interact with instructors and peers.', NOW(), NOW() + INTERVAL '20 days', 2, 'Low', 22);

-- Tasks for Rui (User 4)

-- AI-Powered Analytics Tool
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Develop AI Model for Predictions', 'Create an AI model that predicts trends based on historical data.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 23),
                                                                                                                     ('Data Preprocessing and Cleaning', 'Clean and preprocess the raw data for analysis by the AI model.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 23),
                                                                                                                     ('Set Up Real-Time Data Ingestion', 'Implement a real-time data ingestion system for continuous data updates.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 23),
                                                                                                                     ('Visualize Predictive Insights', 'Create dashboards to visualize the predictive insights generated by the AI model.', NOW(), NOW() + INTERVAL '15 days', 3, 'Medium', 23),
                                                                                                                     ('Test and Optimize AI Performance', 'Test and optimize the AI model’s performance to improve accuracy.', NOW(), NOW() + INTERVAL '20 days', 1, 'Low', 23);

-- Big Data Platform
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Big Data Architecture', 'Design the architecture for processing and analyzing large datasets.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 24),
                                                                                                                     ('Set Up Distributed Computing', 'Implement a distributed computing framework (e.g., Hadoop or Spark).', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 24),
                                                                                                                     ('Integrate Data Sources', 'Integrate various data sources into the platform for analysis.', NOW(), NOW() + INTERVAL '12 days', 1, 'Medium', 24),
                                                                                                                     ('Implement Data Storage System', 'Set up a scalable data storage system for large datasets.', NOW(), NOW() + INTERVAL '15 days', 2, 'Medium', 24),
                                                                                                                     ('Optimize Query Performance', 'Optimize SQL and NoSQL queries for faster data retrieval and processing.', NOW(), NOW() + INTERVAL '20 days', 3, 'Low', 24);

-- CI/CD Pipeline Setup
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Set Up Version Control System', 'Configure Git and set up branching strategies for the project.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 25),
                                                                                                                     ('Implement Continuous Integration', 'Set up a CI pipeline to automate builds and testing.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 25),
                                                                                                                     ('Set Up Continuous Deployment', 'Implement a CD pipeline to automate the deployment process.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 25),
                                                                                                                     ('Configure Automated Testing', 'Integrate automated testing tools to ensure code quality.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 25),
                                                                                                                     ('Monitor CI/CD Performance', 'Monitor the performance of the CI/CD pipeline to ensure efficiency.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 25);

-- Microservices Architecture
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Microservices Architecture', 'Design the architecture for the microservices-based system.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 26),
                                                                                                                     ('Create Service Communication Layer', 'Implement a communication layer for inter-service communication (e.g., REST APIs, gRPC).', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 26),
                                                                                                                     ('Implement Service Discovery', 'Set up service discovery to allow microservices to find each other.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 26),
                                                                                                                     ('Set Up API Gateway', 'Configure an API gateway to route requests to the correct microservices.', NOW(), NOW() + INTERVAL '15 days', 2, 'Medium', 26),
                                                                                                                     ('Containerize Microservices', 'Dockerize each microservice for containerized deployment.', NOW(), NOW() + INTERVAL '20 days', 3, 'Low', 26);

-- Cloud Service Management
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Set Up Cloud Infrastructure', 'Provision cloud resources (e.g., AWS, GCP) for hosting services.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 27),
                                                                                                                     ('Monitor Cloud Resources', 'Implement monitoring for cloud resources to track usage and costs.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 27),
                                                                                                                     ('Set Up Auto-Scaling', 'Implement auto-scaling for cloud services based on demand.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 27),
                                                                                                                     ('Cloud Security Best Practices', 'Ensure cloud resources are secure by applying security best practices.', NOW(), NOW() + INTERVAL '15 days', 3, 'Medium', 27),
                                                                                                                     ('Optimize Cloud Costs', 'Analyze cloud usage and optimize resource allocation to reduce costs.', NOW(), NOW() + INTERVAL '20 days', 3, 'Low', 27);

-- Data Visualization Tool
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Develop Visualization Dashboard', 'Create a dashboard for displaying visualizations of large datasets.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 28),
                                                                                                                     ('Integrate Data Sources', 'Integrate various data sources to display in the dashboard.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 28),
                                                                                                                     ('Add Graphical Representation Features', 'Implement features for graphical representations such as bar charts and line graphs.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 28),
                                                                                                                     ('Implement Real-Time Data Visualization', 'Develop real-time data visualization capabilities for dynamic datasets.', NOW(), NOW() + INTERVAL '15 days', 1, 'Low', 28),
                                                                                                                     ('User Customization Options', 'Allow users to customize the dashboard layout and visualizations.', NOW(), NOW() + INTERVAL '20 days', 3, 'Low', 28);

-- Predictive Modeling System
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Build Predictive Models', 'Develop predictive models for analyzing large datasets.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 29),
                                                                                                                     ('Validate Models', 'Test and validate the predictive models against real-world data.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 29),
                                                                                                                     ('Develop Model Deployment Pipeline', 'Create a pipeline for deploying predictive models into production.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 29),
                                                                                                                     ('Create Model Evaluation Tools', 'Implement tools to evaluate the performance of predictive models.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 29),
                                                                                                                     ('Optimize Model Performance', 'Fine-tune the models for better accuracy and performance.', NOW(), NOW() + INTERVAL '20 days', 1, 'Low', 29);

-- Data Warehousing Solution
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Data Warehouse Schema', 'Design the schema for the data warehouse.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 30),
                                                                                                                     ('Set Up ETL Pipeline', 'Create an ETL pipeline to extract, transform, and load data into the warehouse.', NOW(), NOW() + INTERVAL '10 days', 2, 'High', 30),
                                                                                                                     ('Optimize Data Storage', 'Implement strategies to optimize data storage and retrieval in the warehouse.', NOW(), NOW() + INTERVAL '12 days', 2, 'Medium', 30),
                                                                                                                     ('Create Data Querying System', 'Develop a system for querying and retrieving data from the data warehouse.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 30),
                                                                                                                     ('Implement Data Backup and Recovery', 'Set up backup and recovery procedures for the data warehouse.', NOW(), NOW() + INTERVAL '20 days', 1, 'Low', 30);

-- Tasks for Ricardo (User 5)

-- Customer Feedback Platform
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Set Up Feedback Collection Form', 'Design and implement a feedback collection form for customers.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 31),
                                                                                                                     ('Analyze Feedback Trends', 'Analyze the collected feedback to identify trends and key insights.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 31),
                                                                                                                     ('Implement Feedback Reporting Dashboard', 'Create a dashboard for visualizing and reporting the feedback trends.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 31);

-- Product Launch Tracker
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Product Launch Workflow', 'Design the workflow for tracking the stages of a product launch.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 32),
                                                                                                                     ('Integrate with Product Database', 'Integrate the tracker with the product database to fetch launch data automatically.', NOW(), NOW() + INTERVAL '7 days', 2, 'Medium', 32),
                                                                                                                     ('Create Launch Performance Reports', 'Develop reports to analyze the success and performance of each product launch.', NOW(), NOW() + INTERVAL '10 days', 3, 'Low', 32);

-- Tasks for Nuno (User 6)

-- Social Media Scheduler
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Integrate Social Media APIs', 'Integrate with APIs from various social media platforms to schedule posts.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 33),
                                                                                                                     ('Design Post Scheduling Interface', 'Design the user interface for scheduling and managing posts across platforms.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 33),
                                                                                                                     ('Implement Social Media Post Analytics', 'Create a system to track the performance of scheduled posts on social media.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 33);

-- Email Campaign Manager (Completed project)
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Integrate Email Service Provider', 'Integrate the platform with an email service provider like Mailchimp or SendGrid.', NOW(), NOW() - INTERVAL '3 days', 2, 'High', 34),
                                                                                                                     ('Design Email Templates', 'Create customizable email templates for different marketing campaigns.', NOW(), NOW() - INTERVAL '5 days', 3, 'Medium', 34),
                                                                                                                     ('Implement Campaign Reporting', 'Build a reporting system to track the performance of email campaigns.', NOW(), NOW() - INTERVAL '7 days', 1, 'Low', 34);

-- SEO Performance Tracker
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Integrate with Google Analytics', 'Integrate the SEO performance tracker with Google Analytics to pull website data.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 35),
                                                                                                                     ('Create SEO Metrics Dashboard', 'Design and implement a dashboard for displaying key SEO performance metrics.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 35),
                                                                                                                     ('Optimize SEO Tracking Algorithm', 'Enhance the algorithm to track SEO improvements and ranking changes more accurately.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 35);

-- Tasks for Miguel (User 7)

-- Brand Identity Platform
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Define Brand Identity Guidelines', 'Create a comprehensive set of brand identity guidelines for businesses to follow.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 36),
                                                                                                                     ('Design Logo Variations', 'Design multiple logo variations for different business needs and contexts.', NOW(), NOW() + INTERVAL '15 days', 2, 'Medium', 36),
                                                                                                                     ('Build Brand Style Guide', 'Develop a style guide that covers typography, color palettes, and logo usage.', NOW(), NOW() + INTERVAL '20 days', 3, 'Low', 36);

-- Interactive Web Design
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Interactive Prototypes', 'Create interactive prototypes for web design that showcase dynamic user interactions.', NOW(), NOW() - INTERVAL '5 days', 2, 'Medium', 37),
                                                                                                                     ('Implement Responsive Design', 'Ensure the designs are responsive and work seamlessly across different devices.', NOW(), NOW() - INTERVAL '10 days', 1, 'High', 37);

-- UI Design Kit
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Create UI Components Library', 'Design a library of reusable UI components for web and mobile interfaces.', NOW(), NOW() + INTERVAL '7 days', 1, 'High', 38),
                                                                                                                     ('Document UI Guidelines', 'Document best practices and guidelines for using the UI components in various projects.', NOW(), NOW() + INTERVAL '14 days', 2, 'Medium', 38),
                                                                                                                     ('Build Interactive UI Kit', 'Develop an interactive UI kit that designers can use to prototype designs quickly.', NOW(), NOW() + INTERVAL '21 days', 3, 'Low', 38);

-- Tasks for Adalberto (User 8)

-- Cloud Infrastructure Automation
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Automation Workflow', 'Design a detailed workflow for automating cloud infrastructure provisioning.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 39),
                                                                                                                     ('Implement Cloud Automation Tools', 'Integrate tools for automating infrastructure management on cloud platforms.', NOW(), NOW() + INTERVAL '20 days', 2, 'Medium', 39),
                                                                                                                     ('Test Cloud Automation Scenarios', 'Perform tests on various cloud automation scenarios to ensure robustness and reliability.', NOW(), NOW() + INTERVAL '30 days', 3, 'Low', 39);

-- DevOps Dashboard
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Dashboard UI', 'Design the user interface for the DevOps monitoring dashboard with relevant metrics and KPIs.', NOW(), NOW() - INTERVAL '5 days', 2, 'Medium', 40),
                                                                                                                     ('Integrate Monitoring Tools', 'Integrate monitoring tools like Prometheus and Grafana into the DevOps dashboard.', NOW(), NOW() - INTERVAL '10 days', 1, 'High', 40);

-- Continuous Integration Platform
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Set Up CI Pipeline', 'Set up a continuous integration pipeline for automated testing and code integration.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 41),
                                                                                                                     ('Integrate Code Quality Tools', 'Integrate tools such as SonarQube for automated code quality checks in the CI pipeline.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 41),
                                                                                                                     ('Configure Deployment Automation', 'Configure automated deployment tools for seamless code deployment in production.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 41);

-- Tasks for Serafim (User 9)

-- Distributed Systems Framework
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Distributed System Architecture', 'Design the architecture for the distributed systems framework, focusing on scalability and fault tolerance.', NOW(), NOW() - INTERVAL '10 days', 2, 'High', 42),
                                                                                                                     ('Implement Distributed Communication Protocol', 'Implement a communication protocol for efficient data exchange between distributed system components.', NOW(), NOW() - INTERVAL '15 days', 3, 'Medium', 42),
                                                                                                                     ('Test Fault Tolerance Mechanisms', 'Test and validate the fault tolerance mechanisms implemented in the distributed systems framework.', NOW(), NOW() - INTERVAL '20 days', 1, 'Low', 42);

-- Cloud Native Architecture
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Define Cloud Native Design Patterns', 'Define and document the design patterns for developing cloud-native applications.', NOW(), NOW() + INTERVAL '10 days', 1, 'High', 43),
                                                                                                                     ('Set Up Kubernetes Cluster', 'Set up and configure a Kubernetes cluster for deploying cloud-native applications.', NOW(), NOW() + INTERVAL '20 days', 2, 'Medium', 43),
                                                                                                                     ('Implement Microservices for Cloud-Native Apps', 'Develop microservices architecture for cloud-native applications deployment on the platform.', NOW(), NOW() + INTERVAL '30 days', 3, 'Low', 43);

-- Tasks for Rodrigo (User 10)

-- IT Infrastructure Optimization
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Assess IT Infrastructure Performance', 'Analyze the current performance of IT infrastructure to identify optimization opportunities.', NOW(), NOW() - INTERVAL '5 days', 2, 'High', 44),
                                                                                                                     ('Implement Infrastructure Automation', 'Automate repetitive infrastructure tasks to improve efficiency and reduce manual intervention.', NOW(), NOW() - INTERVAL '10 days', 1, 'Medium', 44),
                                                                                                                     ('Monitor IT Infrastructure Utilization', 'Monitor resource utilization (CPU, memory, storage) of IT infrastructure and propose improvements.', NOW(), NOW() - INTERVAL '12 days', 3, 'Low', 44);

-- Server Monitoring Dashboard
INSERT INTO task (task_title, task_description, created_at, deadline, task_status_id, task_priority, project_id) VALUES
                                                                                                                     ('Design Server Monitoring Dashboard UI', 'Create the user interface for the server monitoring dashboard, focusing on usability and data visualization.', NOW(), NOW() + INTERVAL '5 days', 1, 'High', 45),
                                                                                                                     ('Integrate Server Metrics Collection', 'Implement integration to collect and display server health metrics such as CPU, RAM, disk usage, etc.', NOW(), NOW() + INTERVAL '10 days', 2, 'Medium', 45),
                                                                                                                     ('Configure Alerting for Server Failures', 'Set up alerts and notifications for critical server failures to ensure timely response and resolution.', NOW(), NOW() + INTERVAL '15 days', 3, 'Low', 45);
INSERT INTO project_role (user_id, project_id, user_role)
VALUES
    (3, 1, 'ProjectCreator'),
    (3, 2, 'ProjectCreator'),
    (3, 3, 'ProjectCreator'),
    (3, 4, 'ProjectCreator'),
    (3, 5, 'ProjectCreator'),
    (3, 6, 'ProjectCreator'),
    (3, 7, 'ProjectCreator'),
    (3, 8, 'ProjectMember'),
    (3, 9, 'ProjectMember'),
    (3, 12, 'ProjectMember'),
    (3, 14, 'ProjectMember'),
    (3, 15, 'ProjectMember'),
    (3, 18, 'ProjectMember'),
    (3, 21, 'ProjectMember'),
    (3, 23, 'ProjectMember'),
    (3, 24, 'ProjectMember'),
    (3, 25, 'ProjectMember'),
    (3, 27, 'ProjectMember'),
    (4, 8, 'ProjectCreator'),
    (4, 9, 'ProjectCreator'),
    (4, 10, 'ProjectCreator'),
    (4, 11, 'ProjectCreator'),
    (4, 12, 'ProjectCreator'),
    (4, 13, 'ProjectCreator'),
    (4, 14, 'ProjectCreator'),
    (4, 15, 'ProjectCreator'),
    (4, 1, 'ProjectMember'),
    (4, 2, 'ProjectMember'),
    (4, 3, 'ProjectMember'),
    (4, 5, 'ProjectMember'),
    (4, 7, 'ProjectMember'),
    (4, 17, 'ProjectMember'),
    (4, 18, 'ProjectMember'),
    (4, 20, 'ProjectMember'),
    (4, 28, 'ProjectMember'),
    (4, 30, 'ProjectMember'),
    (5, 16, 'ProjectCreator'),
    (5, 17, 'ProjectCreator'),
    (5, 18, 'ProjectCreator'),
    (5, 19, 'ProjectCreator'),
    (5, 20, 'ProjectCreator'),
    (5, 21, 'ProjectCreator'),
    (5, 22, 'ProjectCreator'),
    (5, 1, 'ProjectMember'),
    (5, 3, 'ProjectMember'),
    (5, 4, 'ProjectMember'),
    (5, 6, 'ProjectMember'),
    (5, 7, 'ProjectMember'),
    (5, 23, 'ProjectMember'),
    (5, 24, 'ProjectMember'),
    (5, 27, 'ProjectMember'),
    (5, 30, 'ProjectMember');
