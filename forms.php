<?php

declare(strict_types=1);

defined('MOODLE_INTERNAL') || die();
			//                                                                       //
			// This file is part of Moodle - http://moodle.org/                      //
			// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
			//                                                                       //
			// Moodle is free software: you can redistribute it and/or modify        //
			// it under the terms of the GNU General Public License as published by  //
			// the Free Software Foundation, either version 3 of the License, or     //
			//                                                                       //
			// Moodle is distributed in the hope that it will be useful,             //
			// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
			// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
			// GNU General Public License for more details.                          //
			//                                                                       //
			// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
			//                                                                       //
			///////////////////////////////////////////////////////////////////////////

			/**
			 * Forms for profilepic Block
			 *
			 * @package    block_profilepic
			 * @author     Justin Hunt <poodllsupport@gmail.com>
			 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
			 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
			 */

			require_once($CFG->libdir . '/formslib.php');

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class block_profilepic_form extends moodleform {

    const BPP_FILENAMECONTROL = 'profilepicfield';
    const BPP_UPLOAD = 1;
    const BPP_SNAPSHOT = 2;
    const BPP_CHOOSE = 3;
    const BPP_WHITEBOARD = 4;

    public function definition(): void {
        global $CFG, $PAGE;

        // Set up form, and get any init data passed in.
        $mform = $this->_form;
        $rectype = $this->_customdata['rectype'];
        $usercontextid = $this->_customdata['usercontextid'];
        $draftitemid = $this->_customdata['draftitemid'];
        $instructions = $this->_customdata['instructions'];
        
        // Add common hidden fields.
        $mform->addElement('hidden', 'draftitemid', $draftitemid);
        $mform->addElement('hidden', 'usercontextid', $usercontextid);
        $mform->addElement('hidden', 'rectype', $rectype);
        $mform->addElement('hidden', 'action', 'doadd');
        $mform->setType('action', PARAM_TEXT);
        $mform->setType('rectype', PARAM_INT);
        $mform->setType('draftitemid', PARAM_INT);
        $mform->setType('usercontextid', PARAM_INT);

        // Add form instructions.
        $mform->addElement('static', 'instructions', '', $instructions);

        // Depending on rectype, show upload box or avatar selection.
        switch ($rectype) {
            case self::BPP_SNAPSHOT:
                $uniqid = \html_writer::random_id();
                $context = ['uniqid' => $uniqid];
                $html = $PAGE->get_renderer('core')->render_from_template('block_profilepic/snapshot', $context);
                
                $mform->addElement('html', $html);
                
                $mform->addElement('hidden', self::BPP_FILENAMECONTROL, '', ['id' => $uniqid . '_hidden']);
                $mform->setType(self::BPP_FILENAMECONTROL, PARAM_RAW);
                break;

            case self::BPP_WHITEBOARD:
                $uniqid = \html_writer::random_id();
                $context = ['uniqid' => $uniqid];
                $html = $PAGE->get_renderer('core')->render_from_template('block_profilepic/whiteboard', $context);
                
                $mform->addElement('html', $html);
                
                $mform->addElement('hidden', self::BPP_FILENAMECONTROL, '', ['id' => $uniqid . '_hidden']);
                $mform->setType(self::BPP_FILENAMECONTROL, PARAM_RAW);
                break;

            case self::BPP_CHOOSE:
                $bigbutton_html = \html_writer::empty_tag('input', [
                    'type' => 'image',
                    'value' => '@@FILENAME@@',
                    'class' => 'yui3-button block_profilepic_button',
                    'id' => 'block_profilepic_@@FILENAME@@_button',
                    'src' => $CFG->wwwroot . '/blocks/profilepic/pix/avatars/@@FILENAME@@.png',
                    'onclick' => "document.getElementById('" . self::BPP_FILENAMECONTROL . "').value='@@FILENAME@@'"
                ]);

                // We could make this dynamic but hardcoded list is fine for now as per original code.
                $boys = [];
                for ($i = 1; $i <= 20; $i++) {
                    $boys[] = 'b' . sprintf('%02d', $i);
                }
                
                $girls = [];
                for ($i = 1; $i <= 20; $i++) {
                    $girls[] = 'g' . sprintf('%02d', $i);
                }

                $boybuttons = [];
                foreach ($boys as $boy) {
                    $buttoncode = str_replace('@@FILENAME@@', 'male/' . $boy, $bigbutton_html);
                    $boybuttons[] = $mform->createElement('static', $boy, '', $buttoncode);
                }
                $girlbuttons = [];
                foreach ($girls as $girl) {
                    $buttoncode = str_replace('@@FILENAME@@', 'female/' . $girl, $bigbutton_html);
                    $girlbuttons[] = $mform->createElement('static', $girl, '', $buttoncode);
                }
                $mform->addGroup($girlbuttons, 'girls_group', get_string('femaleavatars', 'block_profilepic'), [' '], false);
                $mform->addGroup($boybuttons, 'boys_group', get_string('maleavatars', 'block_profilepic'), [' '], false);

                // Add the field to hold the chosen filename.
                // Note: ID was not set in original code explicitly for BPP_CHOOSE but used by JS? 
                // Original: onclick="document.getElementById('profilepicfield').value=...
                // So we need to ensure the ID matches.
                $mform->addElement('hidden', self::BPP_FILENAMECONTROL, '', ['id' => self::BPP_FILENAMECONTROL]);
                $mform->setType(self::BPP_FILENAMECONTROL, PARAM_TEXT);

                // Each avatar is effectively a submit button, so we don't show the standard save/cancel buttons.
                $mform->addElement('cancel');
                return;
                break;

            case self::BPP_UPLOAD:
            default:
                $mform->addElement('filepicker',
                    self::BPP_FILENAMECONTROL,
                    get_string('uploadpicture', 'block_profilepic'),
                    null,
                    ['accepted_types' => 'image']);
                $mform->addRule(self::BPP_FILENAMECONTROL,
                    get_string('musthavefile', 'block_profilepic'),
                    'required',
                    '',
                    'client');
        }

        // Add standard save/cancel buttons for all forms but not the choose avatar form.
        $this->add_action_buttons(true, get_string('do_add_label', 'block_profilepic'));
    }
}