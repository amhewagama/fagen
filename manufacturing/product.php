<?php

$page_security = 'SA_product';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include("product_model.php");
page(_($help_context = "Product"));


simple_page_mode();
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{
    $input_error = 0;
    
    if (strlen($_POST['product_code']) == 0)
{	$input_error = 1;
	display_error(_('product_code cannot be empty.'));
	set_focus('product_code');
}
if (strlen($_POST['product_name']) == 0)
{	$input_error = 1;
	display_error(_('product_name cannot be empty.'));
	set_focus('product_name');
}
if (strlen($_POST['style_id']) == 0)
{	$input_error = 1;
	display_error(_('style_id cannot be empty.'));
	set_focus('style_id');
}
if (strlen($_POST['description']) == 0)
{	$input_error = 1;
	display_error(_('description cannot be empty.'));
	set_focus('description');
}
if (strlen($_POST['inactive']) == 0)
{	$input_error = 1;
	display_error(_('inactive cannot be empty.'));
	set_focus('inactive');
}

    if ($input_error != 1)
    {
        if ($selected_id != -1) 
        {
            update_product($selected_id, $_POST['product_code'], $_POST['product_name'], $_POST['style_id'], $_POST['description'], $_POST['inactive']);
            display_notification(_('product has been updated'));
        } 
        else 
        {
            add_product($_POST['product_code'], $_POST['product_name'], $_POST['style_id'], $_POST['description'], $_POST['inactive']);
            display_notification(_('New product has been added'));
        }
        $Mode = 'RESET';
    }
} 
elseif ($Mode == 'Delete')
{
    $cancel_delete = 0;

    if (!$cancel_delete) 
    {
        delete_product($selected_id);
        display_notification(_('Selected product has been deleted'));
    } 
    $Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
    $selected_id = -1;
	$_POST['product_code'] = '';
	$_POST['product_name'] = '';
	$_POST['style_id'] = '';
	$_POST['description'] = '';
	$_POST['inactive'] = '';
}


$result = get_all_product(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='80%'");

$th = array('Product code', 'Product name', 'Style id', 'Description', 'Inactive','');

table_header($th);    

$k = 0; 
while ($myrow = db_fetch($result)) 
{
    alt_table_row_color($k);

	label_cell($myrow["product_code"], "nowrap");
	label_cell($myrow["product_name"], "nowrap");
	label_cell($myrow["style_id"], "nowrap");
	label_cell($myrow["description"], "nowrap");
	label_cell($myrow["inactive"], "nowrap");
    edit_button_cell("Edit".$myrow["id"], _("Edit"));
    delete_button_cell("Delete".$myrow["id"], _("Delete"));
    end_row(); 
}
end_table(1);

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
    if ($Mode == 'Edit') {  
        $myrow = get_product($selected_id);

		$_POST['product_code'] = $myrow["product_code"];
		$_POST['product_name'] = $myrow["product_name"];
		$_POST['style_id'] = $myrow["style_id"];
		$_POST['description'] = $myrow["description"];
		$_POST['inactive'] = $myrow["inactive"];

    }
    hidden('selected_id', $selected_id);
} 

text_row_ex(_('Product code:'), 'product_code', 25, 55);
text_row_ex(_('Product name:'), 'product_name', 25, 55);
text_row_ex(_('Style id:'), 'style_id', 10, 10);
textarea_row(_('Description:'), 'description', isset($_POST['description'])?$_POST['description']:'', 34, 5);
yesno_list_row('Inactive', 'inactive', isset($_POST['inactive'])?$_POST['inactive']:'');


end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();