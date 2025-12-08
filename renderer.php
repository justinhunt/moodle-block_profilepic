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
class block_profilepic_renderer extends plugin_renderer_base {
	
	/**
	 * Show a form
	 * @param mform $showform the form to display
	 * @param string $heading the title of the form
	 * @param string $message any status messages from previous actions
	 */
	function show_form($showform,$heading, $message=''){
		global $OUTPUT;
	
		//if we have a status message, display it.
		if($message){
			echo $this->output->heading($message,5,'main');
		}
		echo $this->output->heading($heading, 3, 'main');
		$showform->display();
		echo $this->output->footer();
	}
	
	function show_currentpicture($user){
			return $this->output->heading(get_string('currentpicture','block_profilepic'),5,'main') .
			$this->output->user_picture($user,array('size'=>100));
	}
	
	function show_recorderchoices(){
		global $CFG;
		 $def_config = get_config('block_profilepic');
		 
		//GET url and hidden field for button forms.
		$actionurl = new moodle_url('/blocks/profilepic/view.php');
		$h_action = html_writer::tag('input',null,array('type'=>'hidden','name'=>'action', 'value'=>'add'));
		
		//Button template
		$bigbuttontext = html_writer::tag('span','@@BUTTONLABEL@@',  array('class'=>'block_profilepic_buttontext'));	
		$bigbutton_html = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'yui3-button block_profilepic_button','id'=>'block_profilepic_@@BUTTONTYPE@@_button',
		  		'src'=>$CFG->wwwroot . '/blocks/profilepic/pix/@@BUTTONTYPE@@.png'));
		$h_rectype = html_writer::tag('input',null,array('type'=>'hidden','name'=>'rectype', 'value'=>'@@RECTYPE@@'));
		$bigbuttoncontainer = html_writer::tag('div', $bigbutton_html  . 
				$h_action . 
				$h_rectype .
				'<br />' . $bigbuttontext  . 
				'<hr />',array('class'=>'block_profilepic_bigbutton_container'));
		$bigbuttontemplate = html_writer::tag('form',$bigbuttoncontainer,array('action'=>$actionurl->out()));
		
		//create button forms from template
		$search = array('@@RECTYPE@@','@@BUTTONTYPE@@','@@BUTTONLABEL@@');
		$uploadreplace = array(1,'upload',get_string('uploadbuttontext', 'block_profilepic'));
		$choosereplace = array(3,'choose',get_string('choosebuttontext', 'block_profilepic'));

		$uploadbuttonform = str_replace($search, $uploadreplace, $bigbuttontemplate);
		$choosebuttonform = str_replace($search, $choosereplace, $bigbuttontemplate);
		 
		$return = "";
		if($def_config->showupload){ $return .= $uploadbuttonform;}
		if($def_config->showchoose){ $return .= $choosebuttonform;}
		return $return;
	}
	
	function show_profile_link(){
		global $USER,$CFG;

		$chooseurl = new moodle_url('/user/editadvanced.php', array('id'=>$USER->id,'course'=>1));
		$updatelink= html_writer::link($chooseurl, get_string('profilelink', 'block_profilepic'),array('class'=>'block_profilepic_profilelink'));
		return $updatelink;		
	}
	
	function show_picture_linked($user, $page){
	
		
		//$actionurl = '/blocks/profilepic/view.php';
		$actionurl = new moodle_url('/blocks/profilepic/view.php');
		$up =new user_picture($user);
		$up->size=100;
		$picurl = $up->get_url($page);
		
		$chooseurl = new moodle_url($actionurl, array('action'=>'chooserecorder'));
		
		$icon = html_writer::tag('img','',
				array('src'=>$picurl,
				'class'=>'block_profilepic_usericon',
				'alt'=>fullname($user))
				);
	
		$updatelink= html_writer::link($chooseurl,$icon . '<br />' . get_string('viewlinktext', 'block_profilepic'),array('class'=>'block_profilepic_updatelink'));
		return $updatelink;		
		
	}
	
	function show_picture_unlinked($user, $page){

		$up =new user_picture($user);
		$up->size=100;
		$picurl = $up->get_url($page);

		$icon = html_writer::tag('img','',
				array('src'=>$picurl,
				'class'=>'block_profilepic_usericon',
				'alt'=>fullname($user))
				);
	
		$return= $icon;
		return $return;		
		
	}
	
	function show_pic_name($user){
		return '';
		//return $this->output->user_picture($user, array('size'=>55, 'link'=>false))  .fullname($user); 
	}

}
