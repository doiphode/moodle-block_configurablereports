﻿<?php
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

class report_edit_form extends moodleform {
    function definition() {
        global $DB, $USER, $CFG;

        $mform =& $this->_form;
        $id = $this->_customdata['id'];
        $courseid = $this->_customdata['courseid'];
        $reporttype = $this->_customdata['type'];

        $this->general_options();
        
        $this->component_options();

		$mform->addElement('hidden', 'type', $reporttype);
		if (isset($courseid)) {
		    $mform->addElement('hidden', 'courseid', $courseid);
		}
		
		if ($id) {
			$mform->addElement('hidden', 'id', $id);
			$this->add_action_buttons();
		} else {
		    $this->add_action_buttons(true, get_string('add'));
		}
    }
    
    function general_options(){
        $mform =& $this->_form;
        
        $mform->addElement('header', 'reportgeneral', get_string('general', 'form'));
        
        $mform->addElement('text', 'name', get_string('name'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        
        $mform->addElement('htmleditor', 'summary', get_string('summary'), array('rows' => 15));
        $mform->setType('summary', PARAM_RAW);
        
        for ($i=0; $i<=100; $i++) {
            $pagoptions[$i] = $i;
        }
        $mform->addElement('select', 'pagination', get_string("pagination",'block_configurable_reports'), $pagoptions);
        $mform->setDefault('pagination',0);
        $mform->addHelpButton('pagination','pagination', 'block_configurable_reports');
        
        $mform->addElement('checkbox','jsordering',get_string('ordering','block_configurable_reports'),get_string('enablejsordering','block_configurable_reports'));
        $mform->addHelpButton('jsordering','jsordering', 'block_configurable_reports');
    }
    
    function component_options(){
        $mform =& $this->_form;
        
        $report = new stdClass();
        $report->id = $this->_customdata['id'];
        $report->courseid = $this->_customdata['courseid'];
        $report->type = $this->_customdata['type'];
        $reportclass = report_base::get($report);
        
        foreach($reportclass->get_form_components() as $comp => $compclass){
            $compclass->report_form_elements($mform);
        }
    }
	
	function validation($data, $files){
		$errors = parent::validation($data, $files);
		
		// SQL report has special permissions due to full DB access
		if ($data['type'] == 'sql') {
		    if ($this->_customdata['courseid']) {
		        $context = context_course::instance($this->_customdata['courseid']);
		    } else {
		        $context = context_system::instance();
		    }
    		if (!has_capability('block/configurable_reports:managesqlreports', $context)) {
		        $errors[] = 'nosqlpermissions';
		    }
	    }
		
		return $errors;
	}
}

?>