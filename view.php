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

require('../../config.php');
require_once($CFG->dirroot . '/blocks/profilepic/forms.php');
require_once($CFG->libdir.'/gdlib.php');


require_login();
$action = optional_param('action','chooserecorder', PARAM_TEXT); //the user action to take
$rectype = optional_param('rectype', 0,PARAM_TEXT); //the user action to take


global $USER, $COURSE;

//$context = context_course::instance($courseid);
$usercontext = context_user::instance($USER->id);
$PAGE->set_course($COURSE);
$PAGE->set_url('/blocks/profilepic/view.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('changeprofilepicture', 'block_profilepic'));
$PAGE->navbar->add(get_string('editprofilepicture', 'block_profilepic'));

$renderer = $PAGE->get_renderer('block_profilepic');

// OUTPUT
echo $renderer->header();
$message=false;

//only admins and editing teachers should get here really
if(!has_capability('block/profilepic:viewform', $usercontext) ){
	echo $renderer->heading(get_string('inadequatepermissions', 'block_profilepic'), 3, 'main');
	echo $renderer->footer();
	return;
 }

//get our config
$def_config = get_config('block_profilepic');

//prepare fileareas etc
$usercontextid=context_user::instance($USER->id)->id;
$draftitemid = file_get_submitted_draft_itemid(BPP_FILENAMECONTROL);
switch ($rectype){
    case BPP_UPLOAD: $instructions = $def_config->uploadinstructions;break;
    case BPP_CHOOSE: $instructions = $def_config->chooseinstructions;break;
}


switch($action){


	case 'add':

		file_prepare_draft_area($draftitemid, $usercontextid, 'user', 'icon', 0, null,null);
		
		$addform = new block_profilepic_form(null,array('rectype'=>$rectype,
		'usercontextid'=>$usercontextid, 
		'draftitemid'=>$draftitemid,
		'instructions'=>$instructions));
		
		$form_data = new stdClass();
		$addform->set_data($form_data);
		echo $renderer->show_form($addform,get_string('picformheading', 'block_profilepic'));
		return;
	
	
	
	case 'doadd':
		//get add form
		
		$add_form = new block_profilepic_form(null,array('rectype'=>$rectype,
            'usercontextid'=>$usercontextid,
            'draftitemid'=>$draftitemid,
            'instructions'=>$instructions));
		
		if ($add_form->is_cancelled()) {
			$message =  get_string('canceledbyuser','block_profilepic');
			break;
		}
		
		$data = $add_form->get_data();
		$success = false;

		if($data){
			$datavars = get_object_vars($data);
			//this is a bit messy. snapshot stores filename in BPP_filename control
			//as does choose. But choose does not store file suffix there. We have to add it.
			//upload file stores draft item id, so we fuddle all that here
			switch($datavars['rectype']){
				case BPP_UPLOAD:
					$draftitemid= $datavars[BPP_FILENAMECONTROL];
					$file = block_profilepic_open_file($draftitemid);
					break;
				case BPP_CHOOSE:
					if($datavars[BPP_FILENAMECONTROL]){
						$avatarpath = $CFG->dirroot . 
							'/blocks/profilepic/pix/avatars/' . 
							$datavars[BPP_FILENAMECONTROL] .
							'.png';
						$success = block_profilepic_save_profile_image($avatarpath);
					}
					break;
				default:
					$draftitemid= $datavars['draftitemid'];
					$file = block_profilepic_open_file($draftitemid);
			}
			
			//logic branches a bit. Avatars are not in moodle file system
			//so they are done differently.
			if($datavars['rectype']!=BPP_CHOOSE && $file){
				$temppath = $file->copy_content_to_temp();
				$success = block_profilepic_save_profile_image($temppath);
			}		
		}
		if($success){
			//inform user of success
			$message = get_string('addedsuccessfully','block_profilepic');
			//delete temp files. Avatars don't produce temp files.
			if($datavars['rectype']!=BPP_CHOOSE){
				@unlink($temppath);
			}
		}else{
			$message =  get_string('failedtoadd','block_profilepic');
		}
		
	case 'chooserecorder':
	default:
		//Just flow on

}

	//if we have a status message, display it.
	if($message){
		echo $renderer->heading($message,5,'main');
	}
	$user = $DB->get_record('user', array('id'=>$USER->id));
	if($user && $user->picture){
		echo $renderer->show_currentpicture($user);
	}
	echo $renderer->heading(get_string('chooserecorder', 'block_profilepic'), 3, 'main');
	echo $renderer->show_recorderchoices();
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
     * @return object File handler
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

		while ($file = array_shift($files)){
			if($file->get_filename() != '.'){
        		return $file;
        	}
        }
        return false;
    }

/**
 * Try to save the given file (specified by its full path) as the
 * picture for the user with the given id.
 * @param string $originalfile the full path of the picture file.
 *
 * @return mixed new unique revision number or false if not saved
 */
function block_profilepic_save_profile_image($picpath) {
	global $DB, $USER;
	$success = false;
    $context = context_user::instance($USER->id);
    $ret = process_new_icon($context, 'user', 'icon', 0, $picpath);
    if ($ret){
    	$success = $DB->set_field('user', 'picture', $ret, array('id'=>$USER->id));
    }
    return $success;	
}

