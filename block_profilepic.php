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
 * profilepic
 *
 * @package    block_profilepic
 * @copyright  Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_profilepic extends block_base {

    public function init(): void {
        $this->title = get_string('title', 'block_profilepic');
    }

    public function get_content() {
        global $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        // Note: this line seemed unused in original code but keeping context logic if needed.
        // $currentcontext = $this->page->context->get_course_context(false);

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }
        
        $this->title = isset($this->config->title) ? format_text($this->config->title) :
                                format_string(get_string('title', 'block_profilepic'));

        $renderer = $this->page->get_renderer('block_profilepic');
        $def_config = get_config('block_profilepic');

        // Go to profile pic form.
        $user = $DB->get_record('user', ['id' => $USER->id]);
        if ($user) {
            if ($user->picture < 2 || empty($def_config->newonly)) {
                $this->content->text = $renderer->show_picture_linked($user, $this->page);
            } else {
                $this->content->text = $renderer->show_picture_unlinked($user, $this->page);
            }
            
            if (!empty($def_config->showprofilelink)) {
                $this->content->text .= $renderer->show_profile_link();
            }
        }
        return $this->content;
    }

    public function applicable_formats(): array {
        return ['all' => true];
    }

    public function instance_allow_multiple(): bool {
          return true;
    }

    public function has_config(): bool {
        return true;
    }
}
