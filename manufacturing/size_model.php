<?php

function add_size($size_code,$size_name,$description)
{
    $sql = "INSERT INTO size (size_code,size_name,description)
            VALUES (".db_escape($size_code).",".db_escape($size_name).",".db_escape($description).")";

    db_query($sql, "Could not add size");
}

function update_size($id, $size_code,$size_name,$description)
{
    $sql = "UPDATE size SET size_code=".db_escape($size_code).",size_name=".db_escape($size_name).",description=".db_escape($description)."
            WHERE id=".db_escape($id);

    db_query($sql, "Could not update size");
}

function get_all_size($all=false)
{
    $sql = "SELECT * FROM size";

    return db_query($sql, "Could not get all size");
}

function get_size($id)
{
    $sql = "SELECT * FROM size WHERE id=".db_escape($id);

    $result = db_query($sql, "Could not get size");

    return db_fetch($result);
}

function delete_size($id)
{
    $sql="DELETE FROM size WHERE id=".db_escape($id);

    db_query($sql, "Could not delete size");
}

