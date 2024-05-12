<?php

$page_security = 'SA_style_master';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include("style_master_model.php");
page(_($help_context = "Style master"));


simple_page_mode();
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{
    $input_error = 0;
    
    if (strlen($_POST['style_code']) == 0)
{	$input_error = 1;
	display_error(_('style_code cannot be empty.'));
	set_focus('style_code');
}
if (strlen($_POST['style_name']) == 0)
{	$input_error = 1;
	display_error(_('style_name cannot be empty.'));
	set_focus('style_name');
}
if (strlen($_POST['description']) == 0)
{	$input_error = 1;
	display_error(_('description cannot be empty.'));
	set_focus('description');
}
if (strlen($_POST['image_path']) == 0)
{	$input_error = 1;
	display_error(_('image_path cannot be empty.'));
	set_focus('image_path');
}

    if ($input_error != 1)
    {
        if ($selected_id != -1) 
        {
            update_style_master($selected_id, $_POST['style_code'], $_POST['style_name'], $_POST['description'], $_POST['image_path']);
            display_notification(_('style_master has been updated'));
        } 
        else 
        {
            add_style_master($_POST['style_code'], $_POST['style_name'], $_POST['description'], $_POST['image_path']);
            display_notification(_('New style_master has been added'));
        }
        $Mode = 'RESET';
    }
} 
elseif ($Mode == 'Delete')
{
    $cancel_delete = 0;

    if (!$cancel_delete) 
    {
        delete_style_master($selected_id);
        display_notification(_('Selected style_master has been deleted'));
    } 
    $Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
    $selected_id = -1;
	$_POST['style_code'] = '';
	$_POST['style_name'] = '';
	$_POST['description'] = '';
	$_POST['image_path'] = '';
}


$result = get_all_style_master(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='80%'");

$th = array('Style code', 'Style name', 'Description', 'Image path','');

table_header($th);    

$k = 0; 
while ($myrow = db_fetch($result)) 
{
    alt_table_row_color($k);

	label_cell($myrow["style_code"], "nowrap");
	label_cell($myrow["style_name"], "nowrap");
	label_cell($myrow["description"], "nowrap");
	label_cell($myrow["image_path"], "nowrap");
    edit_button_cell("Edit".$myrow["id"], _("Edit"));
    delete_button_cell("Delete".$myrow["id"], _("Delete"));
    end_row(); 
}
end_table(1);

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
    if ($Mode == 'Edit') {  
        $myrow = get_style_master($selected_id);

		$_POST['style_code'] = $myrow["style_code"];
		$_POST['style_name'] = $myrow["style_name"];
		$_POST['description'] = $myrow["description"];
		$_POST['image_path'] = $myrow["image_path"];

    }
    hidden('selected_id', $selected_id);
} 

text_row_ex(_('Style code:'), 'style_code', 25, 55);
text_row_ex(_('Style name:'), 'style_name', 25, 55);
textarea_row(_('Description:'), 'description', isset($_POST['description'])?$_POST['description']:'', 34, 5);
text_row_ex(_('Image path:'), 'image_path', 25, 55);


end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();