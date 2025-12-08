PoodLL Profile Picture
======================
The PoodLL Profile Picture block provides a fast and fun way for students to add or edit their own profile pictures. They can upload a photograph from their computer or mobile device, or choose from a list of 'avatars'. The administrator can configure which options are available to students.

Installation
============
The plugin can be installed in two ways. 
i) By uploading the file via ftp/cpanel to your Moodle server
ii) By uploading the file into Moodle's plugin installer via the Moodle interface.

Uploading via FTP
Unzip the profilepic.zip file into a folder called profilepic. Upload the profilepic folder into the following location:
YOURMOODLESERVER/mod/blocks

Then: 
i) login to your Moodle site as an admin
ii) go to Administration > Site administration > Notifications

Uploading via Moodle's plugin installer
i) login to your Moodle site as an admin
ii) go to Administration > Site administration > Plugins > Install plugins.
iii) Upload the ZIP file, select the 'block' plugin type
iv) tick the acknowledgement checkbox
v) click the button 'Install plugin from the ZIP file'.
vi) Check that you obtain a 'Validation passed!' message, then click the button 'Install plugin'

You will be taken to the Administration > Site administration > Notifications page. 
The installation will begin from there.

Configuration Options
=====================
There are several configuration options for the plugin on the settings page. The settings page is available at:
Administration > Site administration > Plugins > Blocks -> Profile picture

Only Show for New [default=false] - If true, the block will only show when the user has not yet edited their default picture
Show Upload Area [default=true]- Allow users to upload images
Show Choose Avatar [default=true] - Allows users to select an avatar from a list of included avatars.
Instructions for Upload - Intructions for users to be shown above the upload area.
Instructions for Choose Avatar - Intructions for users to be shown when choosing an avatar.

Usage
=====
This mod is a block, and so it will not show anywhere until the block is added to a page or pages. 

From a page on which you want the block to be displayed, click on "Turn Editing On" or "Blocks Editing On." From the list of available blocks in "Add a New Block" find "Profile Picture" and add it to the page.
If you do not wish to show the block, but simply to provide the option to use the edit profile features, you can place the following link in the desired location:
[SITE URL]/blocks/profilepic/view.php

Good Luck, and if you have any questions please contact me, Justin Hunt and poodllsupport@gmail.com.

