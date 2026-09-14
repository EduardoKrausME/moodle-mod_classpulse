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
 * classpulse.php
 *
 * @package   mod_classpulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['allowchange'] = 'Allow students to change their response';
$string['allowchange_help'] = 'When enabled, a new response replaces the participant\'s current response. No response history is retained.';
$string['anonymous'] = 'Anonymous responses';
$string['anonymous_help'] = 'When enabled, teachers only see aggregated results. The response does not store the userid; Moodle keeps an activity-specific pseudonymous identifier only to prevent duplicate responses. Site administrators with direct database and code access should not treat this as absolute cryptographic anonymity.';
$string['anonymousmodecannotchange'] = 'Anonymous mode cannot be changed after the activity has received responses.';
$string['anonymousreportnotice'] = 'Anonymous mode: this report shows aggregated data only.';
$string['anonymousstudentnotice'] = 'This pulse is anonymous to the teacher.';
$string['backtoactivity'] = 'Back to activity';
$string['cannotvote'] = 'You can view this activity but do not have permission to respond.';
$string['changeprompt'] = 'You have already responded. Choosing another option and submitting will replace your current response.';
$string['chartbar'] = 'Bars';
$string['chartline'] = 'Line';
$string['chartpie'] = 'Pie';
$string['charttype'] = 'Chart type';
$string['classpulse:addinstance'] = 'Add a new Class pulse activity';
$string['classpulse:view'] = 'View a Class pulse activity';
$string['classpulse:viewreport'] = 'View the Class pulse aggregate report';
$string['classpulse:vote'] = 'Respond to a Class pulse activity';
$string['classpulsename'] = 'Pulse name';
$string['defaultchart'] = 'Default report chart';
$string['defaultquestion'] = 'How well do you understand this content?';
$string['eventreportviewed'] = 'Pulse report viewed';
$string['eventvotesubmitted'] = 'Pulse response submitted';
$string['identifiedreportnotice'] = 'Identified mode: responses are linked to users in the database, but this dashboard only shows the aggregate distribution.';
$string['invalidcharttype'] = 'Invalid chart type.';
$string['invalidresponse'] = 'Invalid response.';
$string['livereport'] = 'Live report';
$string['missinganonsalt'] = 'The anonymous identifier for this activity could not be generated.';
$string['modulename'] = 'Class pulse';
$string['modulenameplural'] = 'Class pulses';
$string['noclasspulses'] = 'There are no Class pulse activities in this course.';
$string['openlivereport'] = 'Open live report';
$string['pluginadministration'] = 'Class pulse administration';
$string['pluginname'] = 'Class pulse';
$string['privacy:metadata:classpulse_votes'] = 'Stores a participant\'s current Class pulse response.';
$string['privacy:metadata:classpulse_votes:respondenthash'] = 'Activity-specific pseudonymous identifier used to keep one current response per participant.';
$string['privacy:metadata:classpulse_votes:response'] = 'The understanding level selected by the participant.';
$string['privacy:metadata:classpulse_votes:timecreated'] = 'The time when the response was created.';
$string['privacy:metadata:classpulse_votes:timemodified'] = 'The time when the response was last changed.';
$string['privacy:metadata:classpulse_votes:userid'] = 'User ID when the activity is not anonymous; zero when anonymous mode is enabled.';
$string['question'] = 'Question';
$string['response1'] = '😕 I did not understand';
$string['response1short'] = 'Did not understand';
$string['response2'] = '😐 More or less';
$string['response2short'] = 'More or less';
$string['response3'] = '🙂 I understand';
$string['response3short'] = 'Understand';
$string['response4'] = '😄 I master it';
$string['response4short'] = 'Master';
$string['responsecannotchange'] = 'This activity does not allow the response to be changed after submission.';
$string['responselocked'] = 'Your response has already been recorded and cannot be changed.';
$string['responses'] = 'responses';
$string['responsesaved'] = 'Your response has been saved.';
$string['sendresponse'] = 'Send response';
