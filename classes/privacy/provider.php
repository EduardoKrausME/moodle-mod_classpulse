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
 * provider.php
 *
 * @package   mod_classpulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_classpulse\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("classpulse_votes", [
            "userid" => "privacy:metadata:classpulse_votes:userid",
            "respondenthash" => "privacy:metadata:classpulse_votes:respondenthash",
            "response" => "privacy:metadata:classpulse_votes:response",
            "timecreated" => "privacy:metadata:classpulse_votes:timecreated",
            "timemodified" => "privacy:metadata:classpulse_votes:timemodified",
        ], "privacy:metadata:classpulse_votes");
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();
        $anonymousclasspulseids = [];
        $classpulses = $DB->get_records("classpulse", ["anonymous" => 1], "", "id, anonsalt");
        foreach ($classpulses as $classpulse) {
            $respondenthash = hash_hmac("sha256", (string) $userid, $classpulse->anonsalt);
            if ($DB->record_exists("classpulse_votes", [
                "classpulseid" => $classpulse->id,
                "respondenthash" => $respondenthash,
            ])) {
                $anonymousclasspulseids[] = (int) $classpulse->id;
            }
        }

        $params = [
            "contextmodule" => CONTEXT_MODULE,
            "modname" => "classpulse",
            "userid" => $userid,
        ];
        $anonymouscondition = "1 = 0";
        if ($anonymousclasspulseids) {
            [$insql, $inparams] = $DB->get_in_or_equal($anonymousclasspulseids, SQL_PARAMS_NAMED, "anonclasspulse");
            $anonymouscondition = "p.id {$insql}";
            $params += $inparams;
        }

        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextmodule
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {classpulse} p ON p.id = cm.instance
             LEFT JOIN {classpulse_votes} pv ON pv.classpulseid = p.id AND pv.userid = :userid
                 WHERE pv.id IS NOT NULL OR {$anonymouscondition}";
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (!$contextlist->count()) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $cm = get_coursemodule_from_id("classpulse", $context->instanceid, 0, false, MUST_EXIST);
            $classpulse = $DB->get_record("classpulse", ["id" => $cm->instance], "id, anonymous, anonsalt", MUST_EXIST);
            if (!empty($classpulse->anonymous)) {
                $respondenthash = hash_hmac("sha256", (string) $userid, $classpulse->anonsalt);
                $vote = $DB->get_record("classpulse_votes", [
                    "classpulseid" => $classpulse->id,
                    "respondenthash" => $respondenthash,
                ]);
            } else {
                $vote = $DB->get_record("classpulse_votes", ["classpulseid" => $classpulse->id, "userid" => $userid]);
            }
            if (!$vote) {
                continue;
            }
            $data = (object) [
                "response" => $vote->response,
                "timecreated" => transform::datetime($vote->timecreated),
                "timemodified" => transform::datetime($vote->timemodified),
            ];
            writer::with_context($context)->export_data([get_string("pluginname", "mod_classpulse")], $data);
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param \context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("classpulse", $context->instanceid, 0, false, IGNORE_MISSING);
        if ($cm) {
            $DB->delete_records("classpulse_votes", ["classpulseid" => $cm->instance]);
        }
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id("classpulse", $context->instanceid, 0, false, IGNORE_MISSING);
            if ($cm) {
                $classpulse = $DB->get_record("classpulse", ["id" => $cm->instance], "id, anonymous, anonsalt", MUST_EXIST);
                if (!empty($classpulse->anonymous)) {
                    $respondenthash = hash_hmac("sha256", (string) $userid, $classpulse->anonsalt);
                    $DB->delete_records("classpulse_votes", [
                        "classpulseid" => $classpulse->id,
                        "respondenthash" => $respondenthash,
                    ]);
                } else {
                    $DB->delete_records("classpulse_votes", ["classpulseid" => $classpulse->id, "userid" => $userid]);
                }
            }
        }
    }
}
