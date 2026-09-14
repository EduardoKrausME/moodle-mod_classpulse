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
require_capability("mod/classpulse:view", $context);

$PAGE->set_url("/mod/classpulse/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($classpulse->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_activity_record($classpulse);

$event = \mod_classpulse\event\course_module_viewed::create([
    "objectid" => $classpulse->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("classpulse", $classpulse);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

if (optional_param("submitclasspulse", 0, PARAM_BOOL)) {
    require_sesskey();
    require_capability("mod/classpulse:vote", $context);
    $response = required_param("response", PARAM_INT);
    $vote = \mod_classpulse\manager::save_vote($classpulse, $USER->id, $response);

    if (empty($classpulse->anonymous)) {
        $voteevent = \mod_classpulse\event\vote_submitted::create([
            "objectid" => $vote->id,
            "context" => $context,
        ]);
        $voteevent->trigger();
    }

    redirect(new moodle_url("/mod/classpulse/view.php", ["id" => $cm->id]), get_string("responsesaved", "mod_classpulse"));
}

$currentresponse = null;
if (has_capability("mod/classpulse:vote", $context)) {
    $currentresponse = \mod_classpulse\manager::get_user_response($classpulse, $USER->id);
}

$options = [];
foreach (\mod_classpulse\manager::get_responses() as $response) {
    $options[] = [
        "value" => $response,
        "label" => \mod_classpulse\manager::get_response_label($response),
        "selected" => $currentresponse === $response,
    ];
}

$templatecontext = [
    "cmid" => $cm->id,
    "formurl" => (new moodle_url("/mod/classpulse/view.php", ["id" => $cm->id]))->out(false),
    "question" => format_string($classpulse->question, true, ["context" => $context]),
    "intro" => format_module_intro("classpulse", $classpulse, $cm->id),
    "hasintro" => trim($classpulse->intro ?? "") !== "",
    "canvote" => has_capability("mod/classpulse:vote", $context),
    "canreport" => has_capability("mod/classpulse:viewreport", $context),
    "reporturl" => (new moodle_url("/mod/classpulse/report.php", ["id" => $cm->id]))->out(false),
    "sesskey" => sesskey(),
    "options" => $options,
    "hasresponse" => $currentresponse !== null,
    "allowchange" => !empty($classpulse->allowchange),
    "locked" => $currentresponse !== null && empty($classpulse->allowchange),
    "anonymous" => !empty($classpulse->anonymous),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_classpulse/view", $templatecontext);
echo $OUTPUT->footer();
