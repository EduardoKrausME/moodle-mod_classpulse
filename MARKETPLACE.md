# Moodle Plugins directory content

## Description

Class pulse is a Moodle activity for quick understanding checks during a class. Learners answer a configurable question using four comprehension levels, while teachers follow the aggregate distribution in a live report that refreshes automatically.

Teachers can choose whether learners may change an answer, enable anonymous responses, and select the default report chart. The report supports bar, pie, and line visualisations.

In identified mode, the database keeps the user ID with the current response. In anonymous mode, the vote stores user ID `0` and uses an activity-specific pseudonymous HMAC only to enforce one current response per participant; the teacher-facing report receives aggregate counts only.

The activity supports Moodle backup and restore, completion by viewing, the Moodle Privacy API, standard capabilities, and multilingual strings. It requires Moodle 4.5 or later and has no external service dependency.

## Screenshots

Prepared Marketplace screenshots:

- Student activity view: https://github.com/EduardoKrausME/marketplace-plugins/blob/master/screenshots/mod_classpulse/new-1.png
- Teacher/report view: https://github.com/EduardoKrausME/marketplace-plugins/blob/master/screenshots/mod_classpulse/new-2.png
