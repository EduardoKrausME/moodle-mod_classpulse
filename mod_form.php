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
 * mod_form.php
 *
 * @package   mod_classpulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * Class mod_classpulse_mod_form.
 */
class mod_classpulse_mod_form extends moodleform_mod {
    /**
     * Define the activity form.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("classpulsename", "mod_classpulse"), ["size" => "64"]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");
        $mform->addRule("name", get_string("maximumchars", "", 255), "maxlength", 255, "client");

        $this->standard_intro_elements();

        $mform->addElement("textarea", "question", get_string("question", "mod_classpulse"), ["rows" => 3, "cols" => 70]);
        $mform->setType("question", PARAM_TEXT);
        $mform->setDefault("question", get_string("defaultquestion", "mod_classpulse"));
        $mform->addRule("question", null, "required", null, "client");

        $mform->addElement("advcheckbox", "anonymous", get_string("anonymous", "mod_classpulse"));
        $mform->addHelpButton("anonymous", "anonymous", "mod_classpulse");
        $mform->setDefault("anonymous", 0);

        $mform->addElement("advcheckbox", "allowchange", get_string("allowchange", "mod_classpulse"));
        $mform->addHelpButton("allowchange", "allowchange", "mod_classpulse");
        $mform->setDefault("allowchange", 1);

        $mform->addElement("select", "charttype", get_string("defaultchart", "mod_classpulse"), [
            "pie" => get_string("chartpie", "mod_classpulse"),
            "bar" => get_string("chartbar", "mod_classpulse"),
            "line" => get_string("chartline", "mod_classpulse"),
        ]);
        $mform->setDefault("charttype", "bar");

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
