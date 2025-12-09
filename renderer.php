<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Block profilepic renderer.
 * @package   block_profilepic
 * @copyright 2014 Justin Hunt (poodllsupport@gmail.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Block profilepic renderer.
 * @package   block_profilepic
 * @copyright 2014 Justin Hunt (poodllsupport@gmail.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_profilepic_renderer extends plugin_renderer_base {
    
    /**
     * Show a form
     * @param moodleform $showform the form to display
     * @param string $heading the title of the form
     * @param string $message any status messages from previous actions
     */
    public function show_form(moodleform $showform, string $heading, string $message = ''): void {
        // If we have a status message, display it.
        if ($message) {
            echo $this->output->heading($message, 5, 'main');
        }
        echo $this->output->heading($heading, 3, 'main');
        $showform->display();
        echo $this->output->footer();
    }
    
    public function show_currentpicture(stdClass $user): string {
            return $this->output->heading(get_string('currentpicture', 'block_profilepic'), 5, 'main') .
            $this->output->user_picture($user, ['size' => 100]);
    }
    
    public function show_input_choices(int $instanceid = 0): string {
        global $CFG;
         $def_config = get_config('block_profilepic');
         
        // GET url and hidden field for button forms.
        $actionurl = new moodle_url('/blocks/profilepic/view.php');
        $h_action = html_writer::tag('input', null, ['type' => 'hidden', 'name' => 'action', 'value' => 'add']);
        $h_instance = html_writer::tag('input', null, ['type' => 'hidden', 'name' => 'instanceid', 'value' => $instanceid]);
        
        // Button template.
        $bigbuttontext = html_writer::tag('span', '@@BUTTONLABEL@@', ['class' => 'block_profilepic_buttontext']);   
        $bigbutton_html = html_writer::empty_tag('input', ['type' => 'image',
                  'class' => 'yui3-button block_profilepic_button', 'id' => 'block_profilepic_@@BUTTONTYPE@@_button',
                  'src' => $CFG->wwwroot . '/blocks/profilepic/pix/@@BUTTONTYPE@@.png']);
        $h_rectype = html_writer::tag('input', null, ['type' => 'hidden', 'name' => 'rectype', 'value' => '@@RECTYPE@@']);
        $bigbuttoncontainer = html_writer::tag('div', $bigbutton_html  . 
                $h_action . 
                $h_instance .
                $h_rectype .
                '<br />' . $bigbuttontext  . 
                '<hr />', ['class' => 'block_profilepic_bigbutton_container']);
        $bigbuttontemplate = html_writer::tag('form', $bigbuttoncontainer, ['action' => $actionurl->out()]);
        
        // Create button forms from template.
        $search = ['@@RECTYPE@@', '@@BUTTONTYPE@@', '@@BUTTONLABEL@@'];
        $uploadreplace = [1, 'upload', get_string('uploadbuttontext', 'block_profilepic')];
        $choosereplace = [3, 'choose', get_string('choosebuttontext', 'block_profilepic')];
        $snapshotreplace = [2, 'snap', get_string('snapbuttontext', 'block_profilepic')];
        $whiteboardreplace = [4, 'whiteboard', get_string('whiteboardbuttontext', 'block_profilepic')];

        $uploadbuttonform = str_replace($search, $uploadreplace, $bigbuttontemplate);
        $choosebuttonform = str_replace($search, $choosereplace, $bigbuttontemplate);
        $snapshotbuttonform = str_replace($search, $snapshotreplace, $bigbuttontemplate);
        $whiteboardbuttonform = str_replace($search, $whiteboardreplace, $bigbuttontemplate);
         
        $return = "";
        if ($def_config->showsnapshot) {
             $return .= $snapshotbuttonform;
        }
        if ($def_config->showwhiteboard) {
             $return .= $whiteboardbuttonform;
        }
        if ($def_config->showupload) {
             $return .= $uploadbuttonform;
        }
        if ($def_config->showchoose) {
             $return .= $choosebuttonform;
        }
        return $return;
    }
    
    public function show_profile_link(): string {
        global $USER;

        $chooseurl = new moodle_url('/user/editadvanced.php', ['id' => $USER->id, 'course' => 1]);
        $updatelink = html_writer::link($chooseurl, get_string('profilelink', 'block_profilepic'), ['class' => 'block_profilepic_profilelink']);
        return $updatelink;     
    }
    
    public function show_picture_linked(stdClass $user, moodle_page $page, int $instanceid = 0): string {
    
        $actionurl = new moodle_url('/blocks/profilepic/view.php');
        $up = new user_picture($user);
        $up->size = 100;
        $picurl = $up->get_url($page);
        
        $params = ['action' => 'chooserecorder'];
        if ($instanceid) {
            $params['instanceid'] = $instanceid;
        }
        $chooseurl = new moodle_url($actionurl, $params);
        
        $icon = html_writer::tag('img', '',
                ['src' => $picurl,
                'class' => 'block_profilepic_usericon',
                'alt' => fullname($user)]
                );
    
        $updatelink = html_writer::link($chooseurl, $icon . '<br />' . get_string('viewlinktext', 'block_profilepic'), ['class' => 'block_profilepic_updatelink']);
        return $updatelink;     
        
    }
    
    public function show_picture_unlinked(stdClass $user, moodle_page $page): string {

        $up = new user_picture($user);
        $up->size = 100;
        $picurl = $up->get_url($page);

        $icon = html_writer::tag('img', '',
                ['src' => $picurl,
                'class' => 'block_profilepic_usericon',
                'alt' => fullname($user)]
                );
    
        $return = $icon;
        return $return;     
        
    }
    
    public function show_pic_name(stdClass $user): string {
        return '';
    }
}
