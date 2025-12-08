<?php

class block_profilepic_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
		// Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_html'));
        $mform->setType('config_title', PARAM_TEXT);

    }
}
