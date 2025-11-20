Exclence (Learning Platform)

A PHP-based learning platform with courses, progress tracking, productivity tools (notes, todo, timetable), community forum, and mentor Q&A.
Designed for simple deployment on shared hosting (InfinityFree) and local development with XAMPP.

Tech Stack
Languages

PHP (procedural, mysqli)

JavaScript (vanilla)

HTML5, CSS3

Frameworks

None (no Composer/Node frameworks)

Frontend Libraries

Font Awesome 6.5.1 (CDN)

Google Fonts (Poppins, CDN)

Database

MySQL / MariaDB

PHP mysqli extension

Environments

Local: XAMPP on Windows

Production: InfinityFree shared PHP hosting + MySQL

Tooling

No build tools

No npm/yarn/composer

Architecture Diagram
+-------------------------+            HTTP(S)            +-------------------------+
|        Browser          |  <------------------------>   |         PHP App         |
| - HTML/CSS/JS UI        |                              | - PHP pages as routes    |
| - FontAwesome, Poppins  |                              | - Sessions/Auth          |
+-----------+-------------+                              | - Business Logic         |
            |                                            +-----------+-------------+
            | AJAX / Form POST / GET                                 |
            v                                                        v
   +------------------+                                   +------------------------+
   | Static Assets    |                                   |    MySQL/MariaDB       |
   | (css, js, imgs)  |                                   | - users, books, posts  |
   +------------------+                                   | - time logs, progress  |
                                                          +------------------------+

   [Local Dev: XAMPP]                                   [Production: InfinityFree]

Components
Presentation Pages

index.php

dashboard.php

courses.php

progress.php

notes.php

todo.php

timetable.php

library.php

community.php

ask_mentor.php

queries.php

view_query.php

Shared UI

header.php

Static Pages/Assets

course-player.html/css/js

focus-mode.html/js

Additional JS utilities

Application (Server Side)
Authentication & Sessions

login.php

register.php

logout.php

Helpers inside config.php

Feature Endpoints

Progress tracking

Community posts/replies/likes

Mentor Q&A

Time tracking

File downloads

Notes, Todo, Timetable CRUD

Data Access

Direct mysqli usage

connectDB() in config.php

Legacy: db_connection.php (should be removed gradually)

Database Tables
Table	Purpose
users	Authentication, roles
user_time_logs	Track login/logout time
user_sessions	Track session metadata
subjects	Course subjects
user_subject_progress	Course progress
books	Library resources
community_posts	Forum posts
community_replies	Replies
post_likes	Like tracking

Note: course_progress is referenced but not part of schema — create if needed.

Core Features, Value, and Trade-offs
Auth System

Value: Authentication, role-based access (user/mentor)

Trade-off: Procedural sessions; inconsistent session naming

Courses & Player

Value: Users can learn and track progress

Trade-off: Custom JS player without frameworks

Progress & Time Tracking

Value: Helps measure learning efficiency

Trade-off: Two competing models (user_time_logs and course_progress)

Productivity Tools

Notes

To-Do

Timetable

Value: One-stop study hub

Trade-off: Basic CRUD (no reminders/notifications)

Digital Library

Value: In-app reading & downloads

Trade-off: Avoid runtime DDL in setup scripts

Community & Mentor Q&A

Value: Peer support & mentoring

Trade-off: Minimal moderation; requires validation & escaping

Utilities

Focus mode

Whiteboard

Calculator

Setup & Run
Prerequisites

XAMPP on Windows (Apache, PHP 8.x, MySQL)

Browser

1. Place Project
/projectfinal1/exclence

2. Configure Database

Create database:

exclence_local


Update credentials in:

config.php

db_connection.php

database.php (setup-only)

Recommended XAMPP settings:

Parameter	Value
Host	localhost
User	root
Password	(empty)
DB	exclence_local
3. Initialize Schema
Option A — Run Setup Scripts
/setup_database.php
/setup_tables.php
/setup_courses.php
/setup_mentor_tables.php
/setup_community.php
/setup_role.php

Option B — Import SQL

schema.sql

todos.sql

timetable_events.sql

⚠ Important:
database.php contains dangerous DDL (drops tables like books).
Use it ONLY during setup.

4. Run the App

Start Apache & MySQL.

Visit:

Register:
http://localhost/projectfinal1/exclence/register.php

Login:
http://localhost/projectfinal1/exclence/login.php

Dashboard:
/index.php

Environment Variables

Copy:

.env.example → .env


Example config:

APP_ENV=local
APP_DEBUG=false
SITE_NAME=Exclence
BASE_URL=http://localhost/projectfinal1/exclence

DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASS=
DB_NAME=exclence_local

SESSION_NAME=exclence_session

MAIL_DRIVER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=Exclence


Note: Current code uses hardcoded constants.
To use .env, add a loader + fetch values via $_ENV.

Key APIs & Endpoints
Helpers (config.php)

connectDB()

addUser()

authenticateUser()

isLoggedIn()

requireLogin()

Important Endpoints

Auth:

login.php, register.php, logout.php

Core:

index.php, dashboard.php, header.php

Courses:

courses.php, view_course.php, course-player.html

Progress:

progress.php, update_progress.php, track_time.php

Productivity:

notes.php, todo.php, timetable.php

Library:

library.php, read_book.php, download_books.php

Community & Mentor:

community.php, ask_mentor.php, queries.php, view_query.php

Utilities:

whiteboard.php, calculator.php, contact.php, send_email.php

Deployment
Production Hosting

InfinityFree

Live domain example:
🔗 https://exclence.infinityfreeapp.com

Local

XAMPP on Windows

Impact & Metrics
Performance

Lightweight PHP pages

Shared hosting → variable TTFB

Avoid runtime DDL (database.php)

Scale Assumptions

Small-to-moderate users

Single DB instance

No caching or replicas

KPIs to Measure

Page TTFB

Query count & total query time

User engagement

Course completion

Error rate

Quick Wins

Unify DB access using connectDB()

Stop runtime table creation

Remove hardcoded credentials

Fix nav (chatbot.php → chatbot.html)

Add CSRF tokens

Escape output everywhere

Add /health.php and simple logs

Clean unused stubs

Add structured README section per feature
