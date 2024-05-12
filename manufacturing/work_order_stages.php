<?php

$page_security = 'SA_work_order_stages';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include("work_order_stages_model.php");
page(_($help_context = "Work order stages"));


simple_page_mode();
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{
    $input_error = 0;
    
    if (strlen($_POST['stage_name']) == 0)
{	$input_error = 1;
	display_error(_('stage_name cannot be empty.'));
	set_focus('stage_name');
}
if (strlen($_POST['description']) == 0)
{	$input_error = 1;
	display_error(_('description cannot be empty.'));
	set_focus('description');
}
if (strlen($_POST['order_sequence']) == 0)
{	$input_error = 1;
	display_error(_('order_sequence cannot be empty.'));
	set_focus('order_sequence');
}

    if ($input_error != 1)
    {
        if ($selected_id != -1) 
        {
            update_work_order_stages($selected_id, $_POST['stage_name'], $_POST['description'], $_POST['order_sequence']);
            display_notification(_('work_order_stages has been updated'));
        } 
        else 
        {
            add_work_order_stages($_POST['stage_name'], $_POST['description'], $_POST['order_sequence']);
            display_notification(_('New work_order_stages has been added'));
        }
        $Mode = 'RESET';
    }
} 
elseif ($Mode == 'Delete')
{
    $cancel_delete = 0;

    if (!$cancel_delete) 
    {
        delete_work_order_stages($selected_id);
        display_notification(_('Selected work_order_stages has been deleted'));
    } 
    $Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
    $selected_id = -1;
	$_POST['stage_name'] = '';
	$_POST['description'] = '';
	$_POST['order_sequence'] = '';
}


$result = get_all_work_order_stages(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='80%'");

$th = array('Stage name', 'Description', 'Order sequence','');

table_header($th);    

$k = 0; 
while ($myrow = db_fetch($result)) 
{
    alt_table_row_color($k);

	label_cell($myrow["stage_name"], "nowrap");
	label_cell($myrow["description"], "nowrap");
	label_cell($myrow["order_sequence"], "nowrap");
    edit_button_cell("Edit".$myrow["id"], _("Edit"));
    delete_button_cell("Delete".$myrow["id"], _("Delete"));
    end_row(); 
}
end_table(1);

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
    if ($Mode == 'Edit') {  
        $myrow = get_work_order_stages($selected_id);

		$_POST['stage_name'] = $myrow["stage_name"];
		$_POST['description'] = $myrow["description"];
		$_POST['order_sequence'] = $myrow["order_sequence"];

    }
    hidden('selected_id', $selected_id);
} 

text_row_ex(_('Stage name:'), 'stage_name', 25, 55);
textarea_row(_('Description:'), 'description', isset($_POST['description'])?$_POST['description']:'', 34, 5);
text_row_ex(_('Order sequence:'), 'order_sequence', 10, 10);


end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();