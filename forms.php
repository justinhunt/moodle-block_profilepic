<?php
			///////////////////////////////////////////////////////////////////////////
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

			define('BPP_FILENAMECONTROL', 'profilepicfield');
			define('BPP_UPLOAD', 1);
			define('BPP_CHOOSE', 3);

			class block_profilepic_form extends moodleform {

			    public function definition() {
			        global $CFG, $USER, $OUTPUT, $COURSE;

			        // Set up form, and get any init data passed in.
                    $mform = & $this->_form;
                    $rectype = $this->_customdata['rectype'];
                    $usercontextid=$this->_customdata['usercontextid'];
                    $draftitemid = $this->_customdata['draftitemid'];
                    $instructions = $this->_customdata['instructions'];
                    $def_config =  get_config('block_profilepic');

                    //add common hidden fields
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
			            case BPP_CHOOSE:
			                $bigbutton_html = html_writer::empty_tag('input', array(
			                    'type' => 'image',
			                    'value' => '@@FILENAME@@',
			                    'class' => 'yui3-button block_profilepic_button',
			                    'id' => 'block_profilepic_@@FILENAME@@_button',
			                    'src' => $CFG->wwwroot . '/blocks/profilepic/pix/avatars/@@FILENAME@@.png',
			                    'onclick' => "document.getElementById('" . BPP_FILENAMECONTROL . "').value='@@FILENAME@@'"
			                ));

			                $boys = array('b01', 'b02', 'b03', 'b04', 'b05', 'b06', 'b07', 'b08', 'b09', 'b10', 'b11', 'b12', 'b13', 'b14', 'b15', 'b16', 'b17', 'b18', 'b19', 'b20');
			                $girls = array('g01', 'g02', 'g03', 'g04', 'g05', 'g06', 'g07', 'g08', 'g09', 'g10', 'g11', 'g12', 'g13', 'g14', 'g15', 'g16', 'g17', 'g18', 'g19', 'g20');

			                $boybuttons = array();
			                foreach ($boys as $boy) {
			                    $buttoncode = str_replace('@@FILENAME@@', 'male/' . $boy, $bigbutton_html);
			                    $boybuttons[] =& $mform->createElement('static', $boy, '', $buttoncode);
			                }
			                $girlbuttons = array();
			                foreach ($girls as $girl) {
			                    $buttoncode = str_replace('@@FILENAME@@', 'female/' . $girl, $bigbutton_html);
			                    $girlbuttons[] =& $mform->createElement('static', $girl, '', $buttoncode);
			                }
			                $mform->addGroup($girlbuttons, 'girls_group', get_string('femaleavatars', 'block_profilepic'), array(' '), false);
			                $mform->addGroup($boybuttons, 'boys_group', get_string('maleavatars', 'block_profilepic'), array(' '), false);

			                // Add the field to hold the chosen filename.
			                $mform->addElement('hidden', BPP_FILENAMECONTROL, '', array('id' => BPP_FILENAMECONTROL));
			                $mform->setType(BPP_FILENAMECONTROL, PARAM_TEXT);

			                // Each avatar is effectively a submit button, so we don't show the standard save/cancel buttons.
			                $mform->addElement('cancel');
			                return;
			                break;

			            case BPP_UPLOAD:
			            default:
			                $mform->addElement('filepicker',
			                    BPP_FILENAMECONTROL,
			                    get_string('uploadpicture', 'block_profilepic'),
			                    null,
			                    array('accepted_types' => 'image'));
			                $mform->addRule(BPP_FILENAMECONTROL,
			                    get_string('musthavefile', 'block_profilepic'),
			                    'required',
			                    '',
			                    'client');
			        }

			        // Add standard save/cancel buttons for all forms but not the choose avatar form.
			        $this->add_action_buttons(true, get_string('do_add_label', 'block_profilepic'));
			    }
			}