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
 * Controller for various actions of the block.
 *
 * This page displays options for updating your user profile
 *
 * @package    block_profilepic
 * @author     Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

declare(strict_types=1);

require('../../config.php');
require_once($CFG->dirroot . '/blocks/profilepic/forms.php');
require_once($CFG->libdir . '/gdlib.php');


require_login();
$action = optional_param('action', 'chooserecorder', PARAM_TEXT); // The user action to take.
$rectype = optional_param('rectype', 0, PARAM_INT); // The user action to take.
$instanceid = optional_param('instanceid', 0, PARAM_INT);


global $USER, $COURSE, $PAGE, $SITE, $DB;

$usercontext = context_user::instance($USER->id);
$PAGE->set_course($COURSE);
$PAGE->set_url('/blocks/profilepic/view.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('changeprofilepicture', 'block_profilepic'));
$PAGE->navbar->add(get_string('editprofilepicture', 'block_profilepic'));

$renderer = $PAGE->get_renderer('block_profilepic');

// OUTPUT.
echo $renderer->header();
$message = false;

// Only admins and editing teachers should get here really? Wait, this is for users to update their own profile pic.
// The capability check suggests only those who can view form?
// existing code: if(!has_capability('block/profilepic:viewform', $usercontext) ){
if (!has_capability('block/profilepic:viewform', $usercontext)) {
    echo $renderer->heading(get_string('inadequatepermissions', 'block_profilepic'), 3, 'main');
    echo $renderer->footer();
    return;
}

// Get our config.
$def_config = get_config('block_profilepic');
$filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
                             'subdirs'        => 0,
                             'maxfiles'       => 1,
                             'accepted_types' => 'image');

// Prepare fileareas etc.
$usercontextid = context_user::instance($USER->id)->id;
// BPP_FILENAMECONTROL is a constant in the form class now, but we need it here.
// Ideally usage should be cleaned up. usage: block_profilepic_form::BPP_FILENAMECONTROL.
$draftitemid = file_get_submitted_draft_itemid(block_profilepic_form::BPP_FILENAMECONTROL);

$instructions = '';
switch ($rectype) {
    case block_profilepic_form::BPP_UPLOAD:
        $instructions = $def_config->uploadinstructions;
        break;
    case block_profilepic_form::BPP_SNAPSHOT:
        $instructions = $def_config->snapshotinstructions;
        break;
    case block_profilepic_form::BPP_WHITEBOARD:
        $instructions = $def_config->whiteboardinstructions;
        break;
    case block_profilepic_form::BPP_CHOOSE:
        $instructions = $def_config->chooseinstructions;
        break;
}


switch ($action) {

    case 'add':

        file_prepare_draft_area($draftitemid, $usercontextid, 'user', 'icon', 0, $filemanageroptions);
        
        $addform = new block_profilepic_form(null, ['rectype' => $rectype,
        'usercontextid' => $usercontextid, 
        'draftitemid' => $draftitemid,
        'instructions' => $instructions,
        'instanceid' => $instanceid,
        'filemanageroptions' => $filemanageroptions]);
        
        $form_data = new stdClass();
        $addform->set_data($form_data);
        echo $renderer->show_form($addform, get_string('picformheading', 'block_profilepic'));
        return;
    
    
    
    case 'doadd':
        // Get add form.
        
        $add_form = new block_profilepic_form(null, ['rectype' => $rectype,
            'usercontextid' => $usercontextid,
            'draftitemid' => $draftitemid,
            'instructions' => $instructions,
            'instanceid' => $instanceid,
            'filemanageroptions' => $filemanageroptions]);
        
        if ($add_form->is_cancelled()) {
            $message =  get_string('canceledbyuser', 'block_profilepic');
            break;
        }
        
        $data = $add_form->get_data();
        $success = false;

        if ($data) {
            $datavars = get_object_vars($data);
            // This is a bit messy. snapshot stores filename in BPP_filename control
            // as does choose. But choose does not store file suffix there. We have to add it.
            // upload file stores draft item id, so we fuddle all that here.
            switch ($datavars['rectype']) {
                case block_profilepic_form::BPP_UPLOAD:
                    $draftitemid = $datavars[block_profilepic_form::BPP_FILENAMECONTROL];
                    $file = block_profilepic_open_file($draftitemid);
                    break;
                case block_profilepic_form::BPP_CHOOSE:
                    if ($datavars[block_profilepic_form::BPP_FILENAMECONTROL]) {
                        $avatarpath = $CFG->dirroot . 
                            '/blocks/profilepic/pix/avatars/' . 
                            $datavars[block_profilepic_form::BPP_FILENAMECONTROL] .
                            '.png';
                        $success = block_profilepic_save_profile_image($avatarpath);
                    }
                    $file = false; // logic below requires checks
                    break;
                case block_profilepic_form::BPP_SNAPSHOT:
                case block_profilepic_form::BPP_WHITEBOARD:
                    $rawdata = $datavars[block_profilepic_form::BPP_FILENAMECONTROL];
                    // Strip the header "data:image/png;base64,"
                    if (preg_match('/^data:image\/(\w+);base64,/', $rawdata, $type)) {
                        $rawdata = substr($rawdata, strpos($rawdata, ',') + 1);
                        $type = strtolower($type[1]); // jpg, png, gif

                        if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                             // invalid image type
                             $file = false;
                             break;
                        }

                        $rawdata = base64_decode($rawdata);

                        if ($rawdata === false) {
                            $file = false;
                            break;
                        }

                        $tempdir = make_temp_directory('block_profilepic');
                        $tempfile = $tempdir . '/' . md5(uniqid()) . '.png';
                        file_put_contents($tempfile, $rawdata);
                        
                        // We set file to false because we don't have a stored_file object
                        // But we can call save_profile_image right here.
                        $success = block_profilepic_save_profile_image($tempfile);
                        @unlink($tempfile);
                        $file = false; 
                    } else {
                         // invalid data
                         $file = false;
                    }
                    break;

                default:
                    // Should not happen if other cases are removed, but fallback.
                    $draftitemid = $datavars['draftitemid'] ?? 0;
                    $file = block_profilepic_open_file($draftitemid);
            }
            
            // logic branches a bit. Avatars are not in moodle file system
            // so they are done differently.
            if ($datavars['rectype'] != block_profilepic_form::BPP_CHOOSE && $file) {
                // copy_content_to_temp() returns path or false? It returns path or throws exception in recent moodle?
                // Moodle 4.x: stored_file::copy_content_to_temp() returns string|bool.
                $temppath = $file->copy_content_to_temp();
                if ($temppath) {
                    $success = block_profilepic_save_profile_image($temppath);
                    @unlink($temppath);
                }
            }
        }
        if ($success) {
            // Inform user of success.
            $message = get_string('addedsuccessfully', 'block_profilepic');
        } else {
            $message = get_string('failedtoadd', 'block_profilepic');
        }
        // Fall through to default to show options again? 
        // Or maybe redirect? Original code flowed on...
        if ($success && $instanceid) {
            $context = context_block::instance($instanceid);
            $parentcontext = $context->get_parent_context();
            $returnurl = new moodle_url('/');
            $linktext = get_string('home');

            if ($parentcontext->contextlevel == CONTEXT_COURSE) {
                $returnurl = new moodle_url('/course/view.php', ['id' => $parentcontext->instanceid]);
                $course = $DB->get_record('course', ['id' => $parentcontext->instanceid]);
                $linktext = $course->fullname;
            } else if ($parentcontext->contextlevel == CONTEXT_USER) {
                $returnurl = new moodle_url('/user/profile.php');
                $linktext = get_string('profile');
            }
            
            $message .= html_writer::tag('div', 
                html_writer::link($returnurl, get_string('returnto', 'block_profilepic', $linktext, ),['class' => 'btn btn-primary']),
                ['class' => 'usersuccessfullink']
            );
            $message .= '<hr />';
        }
        
    case 'chooserecorder':
    default:
        // Just flow on.

}

// If we have a status message, display it.
if ($message) {
    echo $renderer->heading($message, 5, 'main');
}
$user = $DB->get_record('user', ['id' => $USER->id]);
if ($user && $user->picture) {
    echo $renderer->show_currentpicture($user);
    echo '<hr />';
}
echo $renderer->heading(get_string('chooserecorder', 'block_profilepic'), 3, 'main');
echo $renderer->show_input_choices($instanceid);
echo $renderer->footer();
return;


/**
 * Attempts to open the file
 *
 *  open file using the File API.
 * Return the file handler.
 *
 * 
 * @global object $USER
 * @param int $draftid
 * @return stored_file|false File handler
 */
function block_profilepic_open_file($draftid) {
    global $USER;
    
    $fs = get_file_storage();
    $context = context_user::instance($USER->id);
    $files = $fs->get_area_files($context->id,
                                 'user',
                                 'draft',
                                 $draftid,
                                 'id DESC',
                                 false);
    if (!$files) {
        return false;
    }

    while ($file = array_shift($files)) {
        if ($file->get_filename() != '.') {
            return $file;
        }
    }
    return false;
}

/**
 * Try to save the given file (specified by its full path) as the
 * picture for the user with the given id.
 * @param string $picpath the full path of the picture file.
 *
 * @return bool true if saved, false otherwise
 */
function block_profilepic_save_profile_image($picpath) {
    global $DB, $USER;
    $success = false;
    $context = context_user::instance($USER->id);
    // process_new_icon is a moodle lib function in lib/gdlib.php
    $ret = process_new_icon($context, 'user', 'icon', 0, $picpath);
    if ($ret) {
        $DB->set_field('user', 'picture', $ret, ['id' => $USER->id]);
        $USER->picture = $ret;
        
        // Trigger event for user update.
        \core\event\user_updated::create_from_userid($USER->id)->trigger();
        
        $success = true;
    }
    return $success;    
}

