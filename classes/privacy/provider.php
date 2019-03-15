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
 * Privacy Subsystem implementation for block_taggedcoursesearch.
 *
 * @package    block_taggedcoursesearch
 * @copyright  2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_taggedcoursesearch\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for block_taggedcoursesearch.
 *
 * @copyright 2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\user_preference_provider {

    /**
     * Returns meta-data information about the taggedcoursesearch block.
     *
     * @param  \core_privacy\local\metadata\collection $collection A collection of meta-data.
     * @return \core_privacy\local\metadata\collection Return the collection of meta-data.
     */
    public static function get_metadata(\core_privacy\local\metadata\collection $collection) : \core_privacy\local\metadata\collection {
        $collection->add_user_preference('block_taggedcoursesearch_filter_criteria', 'privacy:metadata:filter_criteria');
        return $collection;
    }

    /**
     * Export all user preferences for the taggedcoursesearch block
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preference = get_user_preferences('block_taggedcoursesearch_filter_criteria', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_taggedcoursesearch', 'block_taggedcoursesearch_filter_criteria',
                $preference, get_string('privacy:metadata:filter_criteria', 'block_taggedcoursesearch'));
        }
    }
}
