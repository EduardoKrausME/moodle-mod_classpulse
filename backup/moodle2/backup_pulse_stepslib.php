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
 * backup_pulse_stepslib.php
 *
 * @package   mod_pulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_pulse_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $pulse = new backup_nested_element("pulse", ["id"], [
            "name", "intro", "introformat", "question", "anonymous", "allowchange", "charttype",
            "anonsalt", "timecreated", "timemodified",
        ]);
        $votes = new backup_nested_element("votes");
        $vote = new backup_nested_element("vote", ["id"], [
            "userid", "respondenthash", "response", "timecreated", "timemodified",
        ]);

        $pulse->add_child($votes);
        $votes->add_child($vote);

        $pulse->set_source_table("pulse", ["id" => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $vote->set_source_sql(
                "SELECT pv.*
                   FROM {pulse_votes} pv
                   JOIN {pulse} p ON p.id = pv.pulseid
                  WHERE pv.pulseid = ? AND p.anonymous = 0",
                [backup::VAR_PARENTID]
            );
            $vote->annotate_ids("user", "userid");
        }

        $pulse->annotate_files("mod_pulse", "intro", null);
        return $this->prepare_activity_structure($pulse);
    }
}
