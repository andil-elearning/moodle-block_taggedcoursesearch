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
 * Block taggedcoursesearch is defined here.
 *
 * @package     block_taggedcoursesearch
 * @copyright   2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
/**
 * taggedcoursesearch block.
 *
 * @package    block_taggedcoursesearch
 * @copyright  2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_taggedcoursesearch extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_taggedcoursesearch');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        if (isset($this->content)) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $action = $this->page->url;
        $action->set_anchor('block-taggedcoursesearch-searchform');
        $searchform = new \block_taggedcoursesearch\form\search_form($action->out(false));
        $renderable = new \block_taggedcoursesearch\output\main($searchform);
        $renderer = $this->page->get_renderer('block_taggedcoursesearch');
        $this->content->text = $renderer->render($renderable);
        return $this->content;
    }
}
