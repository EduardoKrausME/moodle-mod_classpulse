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
 * manager.php
 *
 * @package   mod_pulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_pulse;

use context_module;
use moodle_exception;
use stdClass;

/**
 * Class manager.
 */
class manager {
    /** @var int */
    public const RESPONSE_DID_NOT_UNDERSTAND = 1;

    /** @var int */
    public const RESPONSE_PARTIAL = 2;

    /** @var int */
    public const RESPONSE_UNDERSTOOD = 3;

    /** @var int */
    public const RESPONSE_MASTERED = 4;

    /**
     * Add one pulse instance.
     *
     * @param stdClass $data
     * @return int
     */
    public static function add_instance(stdClass $data): int {
        global $DB;

        self::validate_chart_type((string) $data->charttype);
        $now = time();
        $data->timecreated = $now;
        $data->timemodified = $now;
        $data->anonsalt = bin2hex(random_bytes(32));

        return $DB->insert_record("pulse", $data);
    }

    /**
     * Update one pulse instance.
     *
     * @param stdClass $data
     * @return bool
     */
    public static function update_instance(stdClass $data): bool {
        global $DB;

        $current = $DB->get_record("pulse", ["id" => $data->instance], "id, anonymous", MUST_EXIST);
        if ((int) $current->anonymous !== (int) $data->anonymous &&
                $DB->record_exists("pulse_votes", ["pulseid" => $data->instance])) {
            throw new moodle_exception("anonymousmodecannotchange", "mod_pulse");
        }

        self::validate_chart_type((string) $data->charttype);
        $data->id = $data->instance;
        $data->timemodified = time();
        unset($data->instance);

        return $DB->update_record("pulse", $data);
    }

    /**
     * Delete one pulse instance and its votes.
     *
     * @param int $id
     * @return bool
     */
    public static function delete_instance(int $id): bool {
        global $DB;

        if (!$DB->record_exists("pulse", ["id" => $id])) {
            return false;
        }

        $DB->delete_records("pulse_votes", ["pulseid" => $id]);
        $DB->delete_records("pulse", ["id" => $id]);

        return true;
    }

    /**
     * Save or update a participant response.
     *
     * @param stdClass $pulse
     * @param int $userid
     * @param int $response
     * @return stdClass
     */
    public static function save_vote(stdClass $pulse, int $userid, int $response): stdClass {
        global $DB;

        self::validate_response($response);
        $respondenthash = self::get_respondent_hash($pulse, $userid);
        $existing = $DB->get_record("pulse_votes", [
            "pulseid" => $pulse->id,
            "respondenthash" => $respondenthash,
        ]);

        if ($existing && empty($pulse->allowchange)) {
            throw new moodle_exception("responsecannotchange", "mod_pulse");
        }

        $now = time();
        if ($existing) {
            $existing->response = $response;
            $existing->timemodified = $now;
            $DB->update_record("pulse_votes", $existing);
            return $existing;
        }

        $vote = (object) [
            "pulseid" => $pulse->id,
            "userid" => empty($pulse->anonymous) ? $userid : 0,
            "respondenthash" => $respondenthash,
            "response" => $response,
            "timecreated" => $now,
            "timemodified" => $now,
        ];
        $vote->id = $DB->insert_record("pulse_votes", $vote);

        return $vote;
    }

    /**
     * Get the current response for one participant.
     *
     * @param stdClass $pulse
     * @param int $userid
     * @return int|null
     */
    public static function get_user_response(stdClass $pulse, int $userid): ?int {
        global $DB;

        $respondenthash = self::get_respondent_hash($pulse, $userid);
        $response = $DB->get_field("pulse_votes", "response", [
            "pulseid" => $pulse->id,
            "respondenthash" => $respondenthash,
        ]);

        return $response === false ? null : (int) $response;
    }

    /**
     * Get aggregated response data.
     *
     * @param int $pulseid
     * @return array
     */
    public static function get_distribution(int $pulseid): array {
        global $DB;

        $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $sql = "SELECT response, COUNT(1) AS total
                  FROM {pulse_votes}
                 WHERE pulseid = :pulseid
              GROUP BY response";
        foreach ($DB->get_records_sql($sql, ["pulseid" => $pulseid]) as $record) {
            if (array_key_exists((int) $record->response, $counts)) {
                $counts[(int) $record->response] = (int) $record->total;
            }
        }

        $total = array_sum($counts);
        $items = [];
        foreach ($counts as $response => $count) {
            $items[] = [
                "response" => $response,
                "label" => self::get_response_label($response),
                "shortlabel" => self::get_response_short_label($response),
                "count" => $count,
                "percentage" => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            "total" => $total,
            "items" => $items,
        ];
    }

    /**
     * Return all valid response values.
     *
     * @return int[]
     */
    public static function get_responses(): array {
        return [
            self::RESPONSE_DID_NOT_UNDERSTAND,
            self::RESPONSE_PARTIAL,
            self::RESPONSE_UNDERSTOOD,
            self::RESPONSE_MASTERED,
        ];
    }

    /**
     * Get the localized full label for one response.
     *
     * @param int $response
     * @return string
     */
    public static function get_response_label(int $response): string {
        return match ($response) {
            self::RESPONSE_DID_NOT_UNDERSTAND => get_string("response1", "mod_pulse"),
            self::RESPONSE_PARTIAL => get_string("response2", "mod_pulse"),
            self::RESPONSE_UNDERSTOOD => get_string("response3", "mod_pulse"),
            self::RESPONSE_MASTERED => get_string("response4", "mod_pulse"),
            default => throw new moodle_exception("invalidresponse", "mod_pulse"),
        };
    }

    /**
     * Get a short chart label for one response.
     *
     * @param int $response
     * @return string
     */
    public static function get_response_short_label(int $response): string {
        return match ($response) {
            self::RESPONSE_DID_NOT_UNDERSTAND => get_string("response1short", "mod_pulse"),
            self::RESPONSE_PARTIAL => get_string("response2short", "mod_pulse"),
            self::RESPONSE_UNDERSTOOD => get_string("response3short", "mod_pulse"),
            self::RESPONSE_MASTERED => get_string("response4short", "mod_pulse"),
            default => throw new moodle_exception("invalidresponse", "mod_pulse"),
        };
    }

    /**
     * Check whether a chart type is valid.
     *
     * @param string $charttype
     */
    private static function validate_chart_type(string $charttype): void {
        if (!in_array($charttype, ["pie", "bar", "line"], true)) {
            throw new moodle_exception("invalidcharttype", "mod_pulse");
        }
    }

    /**
     * Check whether a response value is valid.
     *
     * @param int $response
     */
    private static function validate_response(int $response): void {
        if (!in_array($response, self::get_responses(), true)) {
            throw new moodle_exception("invalidresponse", "mod_pulse");
        }
    }

    /**
     * Build a per-activity keyed participant hash.
     *
     * The report never receives this value. It exists only to enforce one current
     * response per authenticated participant without retaining userid in anonymous mode.
     *
     * @param stdClass $pulse
     * @param int $userid
     * @return string
     */
    private static function get_respondent_hash(stdClass $pulse, int $userid): string {
        if (empty($pulse->anonsalt)) {
            throw new moodle_exception("missinganonsalt", "mod_pulse");
        }

        return hash_hmac("sha256", (string) $userid, $pulse->anonsalt);
    }
}
