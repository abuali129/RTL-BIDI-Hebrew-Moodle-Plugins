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

/** Configurable Reports
  * A Moodle block for creating Configurable Reports
  * @package blocks
  * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
  * @date: 2009
  */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class import_form extends moodleform {
    function definition() {
        global $USER, $CFG;

        $mform =& $this->_form;
		$this->set_upload_manager(new upload_manager('userfile', false, false, null, false, 0, true, true, false));
		
        $mform->addElement('header', 'importreport', get_string('importreport', 'block_configurable_reports'));

		$mform->addElement('file', 'userfile', get_string('file'));
		$mform->setType('userfile', PARAM_FILE);
		$mform->addRule('userfile', null, 'required');
		
        // buttons
        $this->add_action_buttons(false, get_string('importreport', 'block_configurable_reports'));

    }
	
	function validation($data, $files){
		$errors = parent::validation($data, $files);
				
		return $errors;
	}		

}

?>