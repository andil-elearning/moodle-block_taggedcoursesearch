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
 * Class containing data for Tagged course search block.
 *
 * @package     block_taggedcoursesearch
 * @copyright   2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 *             based on code from 2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_taggedcoursesearch\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_completion\progress;
use cache;
use completion_info;
use block_taggedcoursesearch\helper;

require_once($CFG->dirroot . '/blocks/taggedcoursesearch/lib.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * Class containing data for Tagged course search block.
 *
 * @copyright   2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * @var string The tab to display.
     */
    protected $searchform;

    /**
     * Constructor.
     *
     * @param \mform $searchform The search form
     */
    public function __construct($searchform) {
        $this->searchform = $searchform;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER;
        $coursesprogress = [];
        $errors = "";

        // Return directly if tags are disable.
        if (empty($CFG->usetags)) {
            return [
                'errors' => $output->render(
                    new \core\output\notification(get_string('tagsaredisabled', 'tag'), \core\output\notification::NOTIFY_ERROR)
                )
            ];
        }

        if ($this->searchform->get_data()) {
            if (($res = helper::set_filter_criteria($this->searchform->get_data()->tags)) !== true) {
                $errors .= $output->render(new \core\output\notification($res, \core\output\notification::NOTIFY_WARNING));
            }
        }
        $filter = new \stdClass();
        $filter->tags = helper::get_filter_criteria();
        $this->searchform->set_data($filter);
        $cache = cache::make('block_taggedcoursesearch', 'filter');
        $filterresult = $cache->get('filter_result');

        if (!empty($filterresult) && $filter->tags == $filterresult['filter']) {
            $courses = $filterresult['result'];
        } else {
            $courses = helper::get_courses($filter);
        }
        foreach ($courses as $courseinlist) {
            $course = get_course($courseinlist->id);
            $completion = new completion_info($course);

            // First, let's make sure completion is enabled.
            if (!$completion->is_enabled()) {
                continue;
            }

            $percentage = progress::get_course_progress_percentage($course);
            if (!is_null($percentage)) {
                $percentage = floor($percentage);
            }

            $coursesprogress[$course->id]['completed'] = $completion->is_course_complete($USER->id);
            $coursesprogress[$course->id]['progress'] = $percentage;
        }

        $coursesview = new courses_view($courses, $coursesprogress);
        $nocoursesurl = $output->image_url('courses', 'block_taggedcoursesearch')->out();

        return [
            'errors' => $errors,
            'searchform' => $this->searchform->render(),
            'coursesview' => $coursesview->export_for_template($output),
            'urls' => [
                'nocourses' => $nocoursesurl,
            ],
        ];
    }
}
