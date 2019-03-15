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
 * Search form renderable.
 *
 * @package     block_taggedcoursesearch
 * @copyright   2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_taggedcoursesearch\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
use moodleform;
/**
 * Search form renderable class.
 *
 * @package     block_taggedcoursesearch
 * @copyright   2018 Arnaud Trouvé <arnaud.trouve@andil.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_form extends moodleform {

    /**
     * Constructor.
     */
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('tags', 'tags', get_string('search_form:tags:label', 'block_taggedcoursesearch'),
            ['itemtype' => 'course', 'component' => 'core'],
            ['length' => '500']);

        $mform->setType('tags', PARAM_TAGLIST);

        $mform->disable_form_change_checker();
        $this->add_action_buttons(false, get_string('search'));
    }
}
