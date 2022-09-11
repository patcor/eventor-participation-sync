# Eventor Participation Sync

This is used to sync a clubs participants in eventor to wordpress database.

# Dependencies
Advanced custom fields - https://www.advancedcustomfields.com

# Instructions to install the plugin
1. Upload the plugin (eventor-participation-sync-php) to wordpress
2. Install custom fields
   - Create a group called 'Eventor'
   - Edit screen options to show field keys
   - Create a text field called 'Url', remember the field key
   - Create a text field called 'Count', remember the field key
   - Create a field called 'EventDate', remember the field key
3. Configure the plugin
   - Enter the API key from Eventor
   - Enter the Organization id from Eventor (can be found https://eventor.orientering.se/OrganisationAdmin/Settings), hover over "edit clubhouse link" to see the id.
   - Enter the three text fields from the custom fields configuration

# Instruction to display information from the plugin
There are many ways to display information in wordpress. I will describe one easy way to do it.

1. Install Code snippets plugin https://wordpress.org/plugins/code-snippets/
2. Add the code from eventor-widget.php
3. Customize the html as you like
3. Add the widget to your sidebar or custom page


# Example image

![Example image](https://github.com/patcor/eventor-participation-sync/blob/main/example.png?raw=true)



