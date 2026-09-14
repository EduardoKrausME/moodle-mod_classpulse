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
 * @package   mod_pulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("pulse", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$pulse = $DB->get_record("pulse", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/pulse:viewreport", $context);

$PAGE->set_url("/mod/pulse/report.php", ["id" => $cm->id]);
$PAGE->set_title(get_string("livereport", "mod_pulse"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_activity_record($pulse);
$PAGE->requires->js_call_amd("mod_pulse/dashboard", "init");

$event = \mod_pulse\event\report_viewed::create([
    "objectid" => $pulse->id,
    "context" => $context,
]);
$event->trigger();

$templatecontext = [
    "cmid" => $cm->id,
    "question" => format_string($pulse->question, true, ["context" => $context]),
    "defaultchart" => $pulse->charttype,
    "anonymous" => !empty($pulse->anonymous),
    "backurl" => (new moodle_url("/mod/pulse/view.php", ["id" => $cm->id]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_pulse/report", $templatecontext);
echo $OUTPUT->footer();
