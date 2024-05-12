import os
import mysql.connector

#Module
MODULE_NAME = 'manufacturing'

#TABLES
#TABLE_NAMES = ["size","product","style_master","work_order_stages"]
TABLE_NAMES = ["size","product","style_master"]

# Database connection details
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASSWORD = ''
DB_NAME = 'fa_db'


if not os.path.exists(f"{MODULE_NAME}"):
    os.makedirs(f"{MODULE_NAME}")

def get_table_schema(table_name):
    try:
        # Replace with your MySQL database credentials
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )

        cursor = connection.cursor()
        cursor.execute(f"DESCRIBE {table_name}")

        schema = []
        for column in cursor.fetchall():
            schema.append(column[0])

        cursor.close()
        connection.close()

        return schema

    except mysql.connector.Error as err:
        print("Error: ", err)
        return None

#MODEL FILE
def generate_model_code(table_name, fields, primary_key):

    field_params = ','.join(f"${field}" for field in fields)

    field_names = ','.join(fields)

    field_values = ','.join(f"\".db_escape(${field}).\"" for field in fields)

    update_fields = ','.join(f"{field}=\".db_escape(${field}).\"" for field in fields)

    model_code_template = f'''<?php

function add_{table_name}({field_params})
{{
    $sql = "INSERT INTO {table_name} ({field_names})
            VALUES ({field_values})";

    db_query($sql, "Could not add {table_name}");
}}

function update_{table_name}($id, {field_params})
{{
    $sql = "UPDATE {table_name} SET {update_fields}
            WHERE {primary_key}=".db_escape($id);

    db_query($sql, "Could not update {table_name}");
}}

function get_all_{table_name}($all=false)
{{
    $sql = "SELECT * FROM {table_name}";

    return db_query($sql, "Could not get all {table_name}");
}}

function get_{table_name}($id)
{{
    $sql = "SELECT * FROM {table_name} WHERE {primary_key}=".db_escape($id);

    $result = db_query($sql, "Could not get {table_name}");

    return db_fetch($result);
}}

function delete_{table_name}($id)
{{
    $sql="DELETE FROM {table_name} WHERE {primary_key}=".db_escape($id);

    db_query($sql, "Could not delete {table_name}");
}}

'''

    return model_code_template, f"{MODULE_NAME}/{table_name}_model.php"

def save_model_code_as_file(file_name, model_code):
    if os.path.exists(file_name):
        os.remove(file_name)

    with open(file_name, "w") as file:
        file.write(model_code)
        print(f"model created: {file_name}")

#CONTROLLER FILE		
def generate_php_file(table_name, field_names, primary_key,control_list):
	
    processed_field_names = ", ".join([f"'{name.replace('_', ' ').capitalize()}'" for name in field_names])

    page_title = table_name.replace("_", " ").capitalize()

    php_file_content = '''<?php

$page_security = 'SA_{table_name}';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include("{table_name}_model.php");
page(_($help_context = "{page_title}"));


simple_page_mode();
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{{
    $input_error = 0;
    
    {error_fields}

    if ($input_error != 1)
    {{
        if ($selected_id != -1) 
        {{
            update_{table_name}($selected_id, {field_params});
            display_notification(_('{table_name} has been updated'));
        }} 
        else 
        {{
            add_{table_name}({field_params});
            display_notification(_('New {table_name} has been added'));
        }}
        $Mode = 'RESET';
    }}
}} 
elseif ($Mode == 'Delete')
{{
    $cancel_delete = 0;

    if (!$cancel_delete) 
    {{
        delete_{table_name}($selected_id);
        display_notification(_('Selected {table_name} has been deleted'));
    }} 
    $Mode = 'RESET';
}} 

if ($Mode == 'RESET')
{{
    $selected_id = -1;
{reset_fields}
}}


$result = get_all_{table_name}(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='80%'");

$th = array({processed_field_names},'');

table_header($th);    

$k = 0; 
while ($myrow = db_fetch($result)) 
{{
    alt_table_row_color($k);

{label_cells}
    edit_button_cell("Edit".$myrow["{primary_key}"], _("Edit"));
    delete_button_cell("Delete".$myrow["{primary_key}"], _("Delete"));
    end_row(); 
}}
end_table(1);

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{{
    if ($Mode == 'Edit') {{  
        $myrow = get_{table_name}($selected_id);

{set_post_fields}

    }}
    hidden('selected_id', $selected_id);
}} 

{control_list}

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();'''

    # Format the placeholders with actual input values
    php_code = php_file_content.format(
        primary_key=primary_key,
        table_name=table_name,
        field_params=', '.join(f'$_POST[\'{field}\']' for field in field_names),
        field_names=', '.join(f"'{field}'," for field in field_names),
        reset_fields='\n'.join(f'\t$_POST[\'{field}\'] = \'\';' for field in field_names),
        label_cells='\n'.join(f'\tlabel_cell($myrow["{field}"], "nowrap");' for field in field_names),
        set_post_fields='\n'.join(f'\t\t$_POST[\'{field}\'] = $myrow["{field}"];' for field in field_names),
        control_list=control_list,
        error_fields="\n".join(
            f"if (strlen($_POST['{field}']) == 0)\n{{"
            f"\t$input_error = 1;\n"
            f"\tdisplay_error(_('{field} cannot be empty.'));\n"
            f"\tset_focus('{field}');\n"
            f"}}"
            for field in field_names
        ),
		page_title=page_title,
		processed_field_names=processed_field_names
    )


    # Save the content to the PHP file 
    file_name = f"{MODULE_NAME}/{table_name}.php"
    with open(file_name, "w") as file:
        file.write(php_code)
        print(f"view created: {file_name}")

#KEYS GENERATE
def detect_foreign_key_tables(table_name, field_names):
    foreign_key_tables = set()
    potential_foreign_key_suffixes = ["_id", "_ID"]
    primary_key = None

    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        cursor = connection.cursor()

        for field in field_names:
            normalized_field = field.strip().lower()
            for suffix in potential_foreign_key_suffixes:
                if normalized_field.endswith(suffix.lower()):
                    # Assume table names might be plural or in a different case
                    referenced_table_base = normalized_field[:-len(suffix)]
                    # Check both singular and plural forms
                    referenced_tables = [referenced_table_base, referenced_table_base + 's']
                    for referenced_table in referenced_tables:
                        query = "SHOW TABLES LIKE %s"
                        cursor.execute(query, (referenced_table,))
                        result = cursor.fetchone()
                        if result:
                            foreign_key_tables.add(result[0])  # Taking the first result
                            print(f"Found foreign key table: {result[0]} for field {field}")

        cursor.execute(f"SHOW KEYS FROM `{table_name}` WHERE Key_name = 'PRIMARY'")
        primary_key_data = cursor.fetchone()
        if primary_key_data:
            primary_key = primary_key_data[4]  # Assuming this is the correct index for column name

        cursor.close()
        connection.close()

    except mysql.connector.Error as err:
        print("Database Error: ", err)

    return list(foreign_key_tables), primary_key


#PRIMARY kEY
def get_primary_key(table_name):
    primary_key = None

    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )

        cursor = connection.cursor()

        cursor.execute(f"SHOW KEYS FROM {table_name} WHERE Key_name = 'PRIMARY'")
        primary_key_data = cursor.fetchone()

        if primary_key_data:
            primary_key = primary_key_data[4]  # Column name of the primary key

        cursor.close()
        connection.close()

    except mysql.connector.Error as err:
        print("PK gen Error: ", err)

    return primary_key

#CONTROLS
def get_control_types(table_name):
    field_types = ''

    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )

        cursor = connection.cursor()
        cursor.execute(f"SHOW COLUMNS FROM {table_name}")
        count = 0;

        for column_data in cursor.fetchall():

            column_name = column_data[0]
            mysql_type = column_data[1]
            label_name = column_name.replace('_', ' ').capitalize()

            field_type = f"text_row_ex(_('{column_name}:'), '{column_name}', 25, 55);\n"

            if mysql_type.startswith("varchar") or mysql_type.startswith("char"):
                field_type = f"text_row_ex(_('{label_name}:'), '{column_name}', 25, 55);\n"
            elif mysql_type.startswith("int"):
                field_type = f"text_row_ex(_('{label_name}:'), '{column_name}', 10, 10);\n"
            elif mysql_type.startswith("float") or mysql_type.startswith("double"):
                field_type = f"text_row_ex(_('{label_name}:'), '{column_name}', 10, 10);\n"
            elif mysql_type.startswith("decimal"):
                field_type = f"text_row_ex(_('{label_name}:'), '{column_name}', 10, 10);\n"
            elif mysql_type.startswith("bool") or mysql_type.startswith("tinyint"):
                field_type = f"yesno_list_row('{label_name}', '{column_name}', isset($_POST['{column_name}'])?$_POST['{column_name}']:'');\n"
            elif mysql_type.startswith("date"):
                field_type = f"date_row(_('{label_name}:'), '{column_name}');\n"
            elif mysql_type.startswith("datetime"):
                field_type = f"date_row(_('{label_name}:'), '{column_name}');\n"
            elif mysql_type.startswith("text"):
                field_type = f"textarea_row(_('{label_name}:'), '{column_name}', isset($_POST['{column_name}'])?$_POST['{column_name}']:'', 34, 5);\n"
            
            if count>0:
                field_types = field_types + field_type

            count=count+1

        cursor.close()
        connection.close()

    except mysql.connector.Error as err:
        print("Controls gen Error: ", err)

    return field_types




def get_foreign_keys(table_name):
    foreign_keys = []
    try:
        # Setup connection
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        cursor = connection.cursor()

        # SQL query to retrieve foreign key information from the INFORMATION_SCHEMA
        query = """
        SELECT
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_NAME = %s AND
            TABLE_SCHEMA = %s AND
            REFERENCED_TABLE_NAME IS NOT NULL;
        """

        # Execute the query
        cursor.execute(query, (table_name, DB_NAME))
        results = cursor.fetchall()

        # Process results
        for (column_name, referenced_table, referenced_column) in results:
            foreign_keys.append({
                'column_name': column_name,
                'referenced_table': referenced_table,
                'referenced_column': referenced_column
            })

        cursor.close()
        connection.close()

    except mysql.connector.Error as err:
        print(f"Error querying database: {err}")

    return foreign_keys



#LIST GENERATE
def list_generate_for_table(field_name, table_name, primary_key):

    list_code='''function {table_name}_x_list($field_name, $selected_id=null, $none_option=false, $submit_on_change=false)
    {{
        $sql = "SELECT id, name FROM {table_name}";

        return combo_input($field_name, $selected_id, $sql, '{primary_key}', 'name',
            array(
                'order' => '{primary_key}',
                'spec_option' => $none_option,
                'spec_id' => ALL_NUMERIC,
                'select_submit'=> $submit_on_change,
                'async' => false,
            ) );
    }}

    function {table_name}_x_list_cells($label, $field_name, $selected_id=null, $none_option=false, $submit_on_change=false)
    {{
        if ($label != null)
            echo "<td>$label</td>";
        echo "<td>";
        echo {table_name}_x_list($field_name, $selected_id, $none_option, $submit_on_change);
        echo "</td>";
    }}

    function {table_name}_list_row($label, $field_name, $selected_id=null, $none_option=false, $submit_on_change=false)
    {{
        echo "<tr><td class='label'>$label</td>";
        {table_name}_x_list_cells(null, $field_name, $selected_id, $none_option, $submit_on_change);
        echo "</tr>";
    }}\n\n
'''
    
    list_code = list_code.format(primary_key=primary_key, table_name=table_name)
    return list_code




def process_one_table(table_name):
    # Get the schema from the MySQL table
    table_schema = get_table_schema(table_name)
    table_schema.pop(0)

    if table_schema:

        primary_key = get_primary_key(table_name)
        #exit()
        list_codes = ''
        foreign_keys_info = get_foreign_keys(table_name)

        # Loop through each foreign key entry and process it
        for fk_info in foreign_keys_info:
            # Concatenate the results
            list_codes = list_codes+list_generate_for_table(fk_info['column_name'], fk_info['referenced_table'], fk_info['referenced_column'])  

        # Generate the model code with the provided schema
        generated_code, file_name = generate_model_code(table_name, table_schema,primary_key)

        model_code = f"{generated_code}{list_codes}"
        # Save the model code
        save_model_code_as_file(file_name, model_code)
        # Save the controller code
        control_list = get_control_types(table_name)
        generate_php_file(table_name, table_schema,primary_key,control_list)  
		
         

    else:
        print("Table not found or unable to retrieve schema.")

if __name__ == "__main__":
    # Get table name from the user
    #table_name_input = input("Enter the table name: ")
    # process_one_table('reservation')

    for table_name in TABLE_NAMES:
        process_one_table(table_name)

