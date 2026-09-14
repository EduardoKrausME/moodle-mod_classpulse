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
 * view.php
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
require_capability("mod/pulse:view", $context);

$PAGE->set_url("/mod/pulse/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($pulse->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_activity_record($pulse);

$event = \mod_pulse\event\course_module_viewed::create([
    "objectid" => $pulse->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("pulse", $pulse);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

if (optional_param("submitpulse", 0, PARAM_BOOL)) {
    require_sesskey();
    require_capability("mod/pulse:vote", $context);
    $response = required_param("response", PARAM_INT);
    $vote = \mod_pulse\manager::save_vote($pulse, $USER->id, $response);

    if (empty($pulse->anonymous)) {
        $voteevent = \mod_pulse\event\vote_submitted::create([
            "objectid" => $vote->id,
            "context" => $context,
        ]);
        $voteevent->trigger();
    }

    redirect(new moodle_url("/mod/pulse/view.php", ["id" => $cm->id]), get_string("responsesaved", "mod_pulse"));
}

$currentresponse = null;
if (has_capability("mod/pulse:vote", $context)) {
    $currentresponse = \mod_pulse\manager::get_user_response($pulse, $USER->id);
}

$options = [];
foreach (\mod_pulse\manager::get_responses() as $response) {
    $options[] = [
        "value" => $response,
        "label" => \mod_pulse\manager::get_response_label($response),
        "selected" => $currentresponse === $response,
    ];
}

$templatecontext = [
    "cmid" => $cm->id,
    "formurl" => (new moodle_url("/mod/pulse/view.php", ["id" => $cm->id]))->out(false),
    "question" => format_string($pulse->question, true, ["context" => $context]),
    "intro" => format_module_intro("pulse", $pulse, $cm->id),
    "hasintro" => trim($pulse->intro ?? "") !== "",
    "canvote" => has_capability("mod/pulse:vote", $context),
    "canreport" => has_capability("mod/pulse:viewreport", $context),
    "reporturl" => (new moodle_url("/mod/pulse/report.php", ["id" => $cm->id]))->out(false),
    "sesskey" => sesskey(),
    "options" => $options,
    "hasresponse" => $currentresponse !== null,
    "allowchange" => !empty($pulse->allowchange),
    "locked" => $currentresponse !== null && empty($pulse->allowchange),
    "anonymous" => !empty($pulse->anonymous),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_pulse/view", $templatecontext);
echo $OUTPUT->footer();
