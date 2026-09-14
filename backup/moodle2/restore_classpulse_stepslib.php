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
 * restore_classpulse_stepslib.php
 *
 * @package   mod_classpulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_classpulse_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $paths = [];
        $paths[] = new restore_path_element("classpulse", "/activity/classpulse");
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("classpulse_vote", "/activity/classpulse/votes/vote");
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_classpulse.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_classpulse($data): void {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();
        $data->anonsalt = bin2hex(random_bytes(32));
        $newitemid = $DB->insert_record("classpulse", $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Method process_classpulse_vote.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_classpulse_vote($data): void {
        global $DB;

        $data = (object) $data;
        $data->classpulseid = $this->get_new_parentid("classpulse");
        $olduserid = (int) $data->userid;
        $data->userid = $this->get_mappingid("user", $olduserid);
        if (empty($data->userid)) {
            return;
        }
        $classpulse = $DB->get_record("classpulse", ["id" => $data->classpulseid], "id, anonsalt", MUST_EXIST);
        $data->respondenthash = hash_hmac("sha256", (string) $data->userid, $classpulse->anonsalt);
        $newitemid = $DB->insert_record("classpulse_votes", $data);
        $this->set_mapping("classpulse_vote", $data->id, $newitemid);
    }

    /**
     * Method after_execute.
     *
     * @return void Return value.
     */
    protected function after_execute(): void {
        $this->add_related_files("mod_classpulse", "intro", null);
    }
}
