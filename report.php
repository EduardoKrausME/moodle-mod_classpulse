<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * report.php
 *
 * @package   mod_classpulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("classpulse", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$classpulse = $DB->get_record("classpulse", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/classpulse:viewreport", $context);

$PAGE->set_url("/mod/classpulse/report.php", ["id" => $cm->id]);
$PAGE->set_title(get_string("livereport", "mod_classpulse"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_activity_record($classpulse);
$PAGE->requires->js_call_amd("mod_classpulse/dashboard", "init");

$event = \mod_classpulse\event\report_viewed::create([
    "objectid" => $classpulse->id,
    "context" => $context,
]);
$event->trigger();

$templatecontext = [
    "cmid" => $cm->id,
    "question" => format_string($classpulse->question, true, ["context" => $context]),
    "defaultchart" => $classpulse->charttype,
    "anonymous" => !empty($classpulse->anonymous),
    "backurl" => (new moodle_url("/mod/classpulse/view.php", ["id" => $cm->id]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_classpulse/report", $templatecontext);
echo $OUTPUT->footer();
