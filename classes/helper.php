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
 * File containing the helper class.
 *
 * @package    block_taggedcoursesearch
 * @copyright  2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_taggedcoursesearch;
defined('MOODLE_INTERNAL') || die();

use block_taggedcoursesearch\form\search_form;
use cache;
use core_tag_area;
use core_tag_tag;
use core_text;
use course_in_list;

/**
 * Class containing a set of helpers.
 *
 * @package    block_taggedcoursesearch
 * @copyright  2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Store filter criteria in cache and try to save it into user preferences
     * @param array $criteria
     * @return bool|string
     */
    public static function set_filter_criteria($criteria) {
        $cache = cache::make('block_taggedcoursesearch', 'filter');
        $cache->set('filter_criteria', $criteria);
        $criteria = implode(',', $criteria);
        if (core_text::strlen($criteria) > 1333) {
            return get_string('user_preference:error:toolong', 'block_taggedcoursesearch');
        }
        return set_user_preference('block_taggedcoursesearch_filter_criteria', $criteria);
    }

    /**
     * Load filter criteria from cache or from user preferences.
     * @return mixed
     */
    public static function get_filter_criteria() {
        $cache = cache::make('block_taggedcoursesearch', 'filter');
        $data = $cache->get('filter_criteria');
        if (!$data) {
            $data = get_user_preferences('block_taggedcoursesearch_filter_criteria');
            if (isset($data)) {
                $data = explode(',', $data);
                $cache->set('filter_criteria', $data);
            }
        }
        return $data;
    }

    /**
     * Get courses corresponding to the given filter
     * @param \stdClass $filter
     * @return array
     */
    public static function get_courses($filter) {
        global $DB;
        $courses = [];
        if ($filter && !empty($filter->tags)) {
            $tagsql = "";
            $coursetagcollection = core_tag_area::get_collection('core', 'course');
            $tagids = array();
            foreach ($filter->tags as $tagname) {
                if (!$tag = core_tag_tag::get_by_name($coursetagcollection, $tagname, '*')) {
                    continue;
                }
                // extra condition to avoid duplication of tags which led to SQL error
                if (!in_array($tag->id, $tagids)) {
                    $tagids[] = $tag->id;
                    $tagsql .= " INNER JOIN {tag_instance} ti" . $tag->id . " ON c.id=ti".$tag->id.".itemid AND ti" . $tag->id . ".tagid=" . $tag->id;
                }
            }
            if (!empty($tagsql)) {
                $rs = $DB->get_recordset_sql("SELECT c.* FROM {course} c " . $tagsql);
                foreach ($rs as $course) {
                    $courses[$course->id] = new \core_course_list_element($course);
                }
                $rs->close();
            }
            $cache = cache::make('block_taggedcoursesearch', 'filter');
            $cache->set('filter_result', ['filter' => $filter->tags, 'result' => $courses]);
        }
        return $courses;
    }
}
