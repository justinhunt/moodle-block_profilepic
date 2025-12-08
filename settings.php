<?php
declare(strict_types=1);

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
 * profilepic block caps.
 *
 * @package    block_profilepic
 * @copyright  Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$settings->add(new admin_setting_configcheckbox('block_profilepic/newonly',
                                                get_string('label_newonly', 'block_profilepic'),
                                                get_string('desc_newonly', 'block_profilepic'),
                                                '0'));

$settings->add(new admin_setting_configcheckbox('block_profilepic/showprofilelink',
                                                get_string('label_showprofilelink', 'block_profilepic'),
                                                get_string('desc_showprofilelink', 'block_profilepic'),
                                                '0'));

$settings->add(new admin_setting_configcheckbox('block_profilepic/showupload',
                                                get_string('label_showupload', 'block_profilepic'),
                                                get_string('desc_showupload', 'block_profilepic'),
                                                '1'));
												
$settings->add(new admin_setting_configcheckbox('block_profilepic/showchoose',
                                                get_string('label_showchoose', 'block_profilepic'),
                                                get_string('desc_showchoose', 'block_profilepic'),
                                                '1'));

$settings->add(new admin_setting_configcheckbox('block_profilepic/showsnapshot',
                                                get_string('label_showsnapshot', 'block_profilepic'),
                                                get_string('desc_showsnapshot', 'block_profilepic'),
                                                '1'));

$settings->add(new admin_setting_configcheckbox('block_profilepic/showwhiteboard',
                                                get_string('label_showwhiteboard', 'block_profilepic'),
                                                get_string('desc_showwhiteboard', 'block_profilepic'),
                                                '1'));
                                                
                                                
$settings->add(new admin_setting_configtextarea('block_profilepic/uploadinstructions',
                                                     get_string('instructions_upload', 'block_profilepic'),
                                                    '',
                                                     get_string('instructions_upload_def', 'block_profilepic')));

$settings->add(new admin_setting_configtextarea('block_profilepic/snapshotinstructions',
                                                     get_string('instructions_snapshot', 'block_profilepic'),
                                                    '',
                                                     get_string('instructions_snapshot_def', 'block_profilepic')));

$settings->add(new admin_setting_configtextarea('block_profilepic/whiteboardinstructions',
                                                     get_string('instructions_whiteboard', 'block_profilepic'),
                                                    '',
                                                     get_string('instructions_whiteboard_def', 'block_profilepic')));

$settings->add(new admin_setting_configtextarea('block_profilepic/chooseinstructions',
                                                     get_string('instructions_choose', 'block_profilepic'),
                                                    '',
                                                     get_string('instructions_choose_def', 'block_profilepic')));

