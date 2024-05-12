<?php

function add_work_order_stages($stage_name,$description,$order_sequence)
{
    $sql = "INSERT INTO work_order_stages (stage_name,description,order_sequence)
            VALUES (".db_escape($stage_name).",".db_escape($description).",".db_escape($order_sequence).")";

    db_query($sql, "Could not add work_order_stages");
}

function update_work_order_stages($id, $stage_name,$description,$order_sequence)
{
    $sql = "UPDATE work_order_stages SET stage_name=".db_escape($stage_name).",description=".db_escape($description).",order_sequence=".db_escape($order_sequence)."
            WHERE id=".db_escape($id);

    db_query($sql, "Could not update work_order_stages");
}

function get_all_work_order_stages($all=false)
{
    $sql = "SELECT * FROM work_order_stages";

    return db_query($sql, "Could not get all work_order_stages");
}

function get_work_order_stages($id)
{
    $sql = "SELECT * FROM work_order_stages WHERE id=".db_escape($id);

    $result = db_query($sql, "Could not get work_order_stages");

    return db_fetch($result);
}

function delete_work_order_stages($id)
{
    $sql="DELETE FROM work_order_stages WHERE id=".db_escape($id);

    db_query($sql, "Could not delete work_order_stages");
}

