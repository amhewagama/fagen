<?php

function add_style_master($style_code,$style_name,$description,$image_path)
{
    $sql = "INSERT INTO style_master (style_code,style_name,description,image_path)
            VALUES (".db_escape($style_code).",".db_escape($style_name).",".db_escape($description).",".db_escape($image_path).")";

    db_query($sql, "Could not add style_master");
}

function update_style_master($id, $style_code,$style_name,$description,$image_path)
{
    $sql = "UPDATE style_master SET style_code=".db_escape($style_code).",style_name=".db_escape($style_name).",description=".db_escape($description).",image_path=".db_escape($image_path)."
            WHERE id=".db_escape($id);

    db_query($sql, "Could not update style_master");
}

function get_all_style_master($all=false)
{
    $sql = "SELECT * FROM style_master";

    return db_query($sql, "Could not get all style_master");
}

function get_style_master($id)
{
    $sql = "SELECT * FROM style_master WHERE id=".db_escape($id);

    $result = db_query($sql, "Could not get style_master");

    return db_fetch($result);
}

function delete_style_master($id)
{
    $sql="DELETE FROM style_master WHERE id=".db_escape($id);

    db_query($sql, "Could not delete style_master");
}

