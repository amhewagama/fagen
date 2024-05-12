<?php

$page_security = 'SA_size';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include("size_model.php");
page(_($help_context = "Size"));


simple_page_mode();
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{
    $input_error = 0;
    
    if (strlen($_POST['size_code']) == 0)
{	$input_error = 1;
	display_error(_('size_code cannot be empty.'));
	set_focus('size_code');
}
if (strlen($_POST['size_name']) == 0)
{	$input_error = 1;
	display_error(_('size_name cannot be empty.'));
	set_focus('size_name');
}
if (strlen($_POST['description']) == 0)
{	$input_error = 1;
	display_error(_('description cannot be empty.'));
	set_focus('description');
}

    if ($input_error != 1)
    {
        if ($selected_id != -1) 
        {
            update_size($selected_id, $_POST['size_code'], $_POST['size_name'], $_POST['description']);
            display_notification(_('size has been updated'));
        } 
        else 
        {
            add_size($_POST['size_code'], $_POST['size_name'], $_POST['description']);
            display_notification(_('New size has been added'));
        }
        $Mode = 'RESET';
    }
} 
elseif ($Mode == 'Delete')
{
    $cancel_delete = 0;

    if (!$cancel_delete) 
    {
        delete_size($selected_id);
        display_notification(_('Selected size has been deleted'));
    } 
    $Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
    $selected_id = -1;
	$_POST['size_code'] = '';
	$_POST['size_name'] = '';
	$_POST['description'] = '';
}


$result = get_all_size(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='80%'");

$th = array('Size code', 'Size name', 'Description','');

table_header($th);    

$k = 0; 
while ($myrow = db_fetch($result)) 
{
    alt_table_row_color($k);

	label_cell($myrow["size_code"], "nowrap");
	label_cell($myrow["size_name"], "nowrap");
	label_cell($myrow["description"], "nowrap");
    edit_button_cell("Edit".$myrow["id"], _("Edit"));
    delete_button_cell("Delete".$myrow["id"], _("Delete"));
    end_row(); 
}
end_table(1);

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
    if ($Mode == 'Edit') {  
        $myrow = get_size($selected_id);

		$_POST['size_code'] = $myrow["size_code"];
		$_POST['size_name'] = $myrow["size_name"];
		$_POST['description'] = $myrow["description"];

    }
    hidden('selected_id', $selected_id);
} 

text_row_ex(_('Size code:'), 'size_code', 25, 55);
text_row_ex(_('Size name:'), 'size_name', 25, 55);
textarea_row(_('Description:'), 'description', isset($_POST['description'])?$_POST['description']:'', 34, 5);


end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();