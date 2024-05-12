<?php

function add_product($product_code,$product_name,$style_id,$description,$inactive)
{
    $sql = "INSERT INTO product (product_code,product_name,style_id,description,inactive)
            VALUES (".db_escape($product_code).",".db_escape($product_name).",".db_escape($style_id).",".db_escape($description).",".db_escape($inactive).")";

    db_query($sql, "Could not add product");
}

function update_product($id, $product_code,$product_name,$style_id,$description,$inactive)
{
    $sql = "UPDATE product SET product_code=".db_escape($product_code).",product_name=".db_escape($product_name).",style_id=".db_escape($style_id).",description=".db_escape($description).",inactive=".db_escape($inactive)."
            WHERE id=".db_escape($id);

    db_query($sql, "Could not update product");
}

function get_all_product($all=false)
{
    $sql = "SELECT * FROM product";

    return db_query($sql, "Could not get all product");
}

function get_product($id)
{
    $sql = "SELECT * FROM product WHERE id=".db_escape($id);

    $result = db_query($sql, "Could not get product");

    return db_fetch($result);
}

function delete_product($id)
{
    $sql="DELETE FROM product WHERE id=".db_escape($id);

    db_query($sql, "Could not delete product");
}

function style_master_x_list($field_name, $selected_id=null, $none_option=false, $submit_on_change=false)
    {
        $sql = "SELECT id, name FROM style_master";

        return combo_input($field_name, $selected_id, $sql, 'id', 'name',
            array(
                'order' => 'id',
                'spec_option' => $none_option,
                'spec_id' => ALL_NUMERIC,
                'select_submit'=> $submit_on_change,
                'async' => false,
            ) );
    }

    function style_master_x_list_cells($label, $field_name, $selected_id=null, $none_option=false, $submit_on_change=false)
    {
        if ($label != null)
            echo "<td>$label</td>";
        echo "<td>";
        echo style_master_x_list($field_name, $selected_id, $none_option, $submit_on_change);
        echo "</td>";
    }

    function style_master_list_row($label, $field_name, $selected_id=null, $none_option=false, $submit_on_change=false)
    {
        echo "<tr><td class='label'>$label</td>";
        style_master_x_list_cells(null, $field_name, $selected_id, $none_option, $submit_on_change);
        echo "</tr>";
    }


