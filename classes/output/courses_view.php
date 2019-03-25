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
 * Class containing data for courses view in the Tagged course search block.
 *
 * @package    block_taggedcoursesearch
 * @copyright  2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 *             based on code from 2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_taggedcoursesearch\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_course\external\course_summary_exporter;
use moodle_url;
use core_tag_tag;

/**
 * Class containing data for courses view in the myoverview block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_view implements renderable, templatable {
    /** Quantity of courses per page. */
    const COURSES_PER_PAGE = 6;

    /** @var array $courses List of courses the user is enrolled in. */
    protected $courses = [];

    /** @var array $coursesprogress List of progress percentage for each course. */
    protected $coursesprogress = [];

    /**
     * The courses_view constructor.
     *
     * @param array $courses list of courses.
     * @param array $coursesprogress list of courses progress.
     */
    public function __construct($courses, $coursesprogress) {
        $this->courses = $courses;
        $this->coursesprogress = $coursesprogress;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $OUTPUT;

        // Build courses view data structure.
        $coursesview = ['hascourses' => !empty($this->courses)];

        $nbcourses = 0;
        foreach ($this->courses as $course) {
            $context = $course->get_context();
            $exporter = new course_summary_exporter(get_course($course->id), [
                'context' => $context
            ]);
            $exportedcourse = $exporter->export($output);
            // Convert summary to plain text.
            $exportedcourse->summary = content_to_text($exportedcourse->summary, $exportedcourse->summaryformat);

            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                if ($isimage) {
                    $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                    $exportedcourse->courseimage = $url;
                    $exportedcourse->classes = 'courseimage';
                    break;
                }
            }

            $exportedcourse->color = $this->coursecolor($course->id);

            if (!isset($exportedcourse->courseimage)) {
                $pattern = new \core_geopattern();
                $pattern->setColor($exportedcourse->color);
                $pattern->patternbyid($course->id);
                $exportedcourse->classes = 'coursepattern';
                $exportedcourse->courseimage = $pattern->datauri();
            }

            // Include course visibility.
            $exportedcourse->visible = (bool)$course->visible;

            $exportedcourse->tags = $OUTPUT->tag_list(
                core_tag_tag::get_item_tags('core', 'course', $course->id),
                get_string('tags', 'block_taggedcoursesearch')
            );

            $courseprogress = null;
            if (isset($this->coursesprogress[$course->id])) {
                $courseprogress = $this->coursesprogress[$course->id]['progress'];
                $exportedcourse->hasprogress = !is_null($courseprogress);
                $exportedcourse->progress = $courseprogress;
            }

                // Courses still in progress. Either their end date is not set, or the end date is not yet past the current date.
                $inprogresspages = floor($nbcourses / $this::COURSES_PER_PAGE);

                $coursesview['pages'][$inprogresspages]['courses'][] = $exportedcourse;
                $coursesview['pages'][$inprogresspages]['active'] = ($inprogresspages == 0 ? true : false);
                $coursesview['pages'][$inprogresspages]['page'] = $inprogresspages + 1;
                $coursesview['haspages'] = true;
                $nbcourses++;
        }

        // Build courses view paging bar structure.
        $quantpages = ceil(count($this->courses) / $this::COURSES_PER_PAGE);

        if ($quantpages) {
            $coursesview['pagingbar']['disabled'] = ($quantpages <= 1);
            $coursesview['pagingbar']['pagecount'] = $quantpages;
            $coursesview['pagingbar']['first'] = ['page' => '&laquo;', 'url' => '#'];
            $coursesview['pagingbar']['last'] = ['page' => '&raquo;', 'url' => '#'];
            for ($page = 0; $page < $quantpages; $page++) {
                $coursesview['pagingbar']['pages'][$page] = [
                    'number' => $page + 1,
                    'page' => $page + 1,
                    'url' => '#',
                    'active' => ($page == 0 ? true : false)
                ];
            }
        }

        return $coursesview;
    }

    /**
     * Generate a semi-random color based on the courseid number (so it will always return
     * the same color for a course)
     *
     * @param int $courseid
     * @return string $color, hexvalue color code.
     */
    protected function coursecolor($courseid) {
        // The colour palette is hardcoded for now. It would make sense to combine it with theme settings.
        $basecolors = [
            '#81ecec', '#74b9ff', '#a29bfe', '#dfe6e9', '#00b894',
            '#0984e3', '#b2bec3', '#fdcb6e', '#fd79a8', '#6c5ce7'
        ];

        $color = $basecolors[$courseid % 10];
        return $color;
    }
}
