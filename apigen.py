import mysql.connector


class DataSet:
    def __init__(self, table, primaryKey, filename):
        self.table = table
        self.primaryKey = primaryKey
        self.filename = filename


def sql_to_camel(string):
    parts = string.split("_")
    name = ""
    for npart in parts:
        name += npart.capitalize()
        #print(name)
    return name


schema = 'gamify'
mydb = mysql.connector.connect(
    host="stark.cse.buffalo.edu",
    user="gamify_user",
    passwd="gamify1$1",
    database=schema
)

genset = [DataSet("users", "user_id", "usercontroller.php"), DataSet("user_badges", "ub_id", "ubcontroller.php")]
path = "C:\\Users\\Alan Hunt\\Documents\\CSE\\CSE 410 HCI - Spring 2019\\webapi\\"

for currentTable in genset:

    table = currentTable.table
    primaryKey = currentTable.primaryKey
    filename = currentTable.filename

    file = open(path + filename, "w")
    imports = ['utils.php', 'connect.php']

    # pull the table and column data
    mycursor = mydb.cursor()
    mycursor.execute(
        "select * from information_schema.columns where table_name = '" + table + "' and table_schema = '" + schema + "'")
    myresult = mycursor.fetchall()

    file.write("<?php\n")
    file.write("\n")

    # build the list of imports
    for imp in imports:
        file.write("require '" + imp + "';\n")

    # set headers
    file.write("\n")
    file.write("// the response will be a JSON object\n")
    file.write("header('Content-Type: application/json');\n")
    file.write("header('Access-Control-Allow-Origin: *');\n")

    file.write("$json = array();\n")

    file.write("// pull the input, which should be in the form of a JSON object\n")
    file.write("$json_params = file_get_contents('php://input');\n")

    file.write("// check to make sure that the JSON is in a valid format\n")
    file.write("if (isValidJSON($json_params)){\n")
    file.write(" //load in all the potential parameters.  These should match the database columns for the objects. \n")
    file.write("  $decoded_params = json_decode($json_params, TRUE);\n")
    file.write("  $action = $decoded_params['action'];\n")
    file.write("  $json['action'] = $action;\n")
    file.write(
        "  // uncomment the following line if you want to turn PHP error reporting on for debug - note, this will break the JSON response\n")
    file.write("  //ini_set('display_errors', 1); error_reporting(-1);\n")

    # Generate Routines

    names = []

    for x in myresult:
        nameparts = x[3].split("_")
        newname = ""
        for part in nameparts:
            newname += part.capitalize()
            newname = newname[:1].lower() + newname[1:]
        file.write("$" + newname + " = \"\";\n")
        file.write("if (array_key_exists('" + newname.lower() + "', $decoded_params)){\n")
        file.write("  $" + newname + " =  $decoded_params['" + newname.lower() + "'];\n")
        file.write("}\n")
        names.append(newname)
        if x[16] == "PRI":
            primarykey = newname.lower()

    file.write("if ($action == \"addOrEdit" + sql_to_camel(table) + "\"){\n")

    file.write("$args = array();\n")
    file.write("if (IsNullOrEmpty($" + names[0] + ")){\n")
    file.write(" $sql = \"INSERT INTO " + table + " (")
    idString = ""
    for x in myresult:
        idString += x[3] + ","
    file.write(idString[:-1])

    file.write(") VALUES ( ")
    bindString = ""
    for x in myresult:
        bindString += "?,"
    file.write(bindString[:-1])
    file.write(");\";\n")

    for x in names:
        file.write("array_push($args, $" + x + ");\n")

    file.write("try{\n")
    file.write("$statement = $conn->prepare($sql);\n")
    file.write("$statement->execute($args);\n")
    file.write("$last_id = $conn->lastInsertId();\n")
    file.write("$json['Record Id'] = $last_id;\n")
    file.write("$json['Status'] = \"SUCCESS - Inserted Id $last_id\";\n")
    file.write("}catch (Exception $e) { \n")
    file.write("    $json['Exception'] =  $e->getMessage();\n")
    file.write("}\n")


    file.write("}else{\n")
    bindString = ""
    file.write("$sql = \"UPDATE " + table + " SET ")
    for i in range(1, len(myresult)):
        bindString += myresult[i][3] + " = ?,"
    file.write(bindString[:-1])
    file.write(" WHERE " + myresult[0][3] + " = ?; \";\n")

    for i in range(1, len(myresult)):
        file.write("array_push($args, $" + names[i] + ");\n")
    file.write("array_push($args, $" + names[0] + ");\n")
    file.write("try{\n")
    file.write("$statement = $conn->prepare($sql);\n")
    file.write("$statement->execute($args);\n")
    file.write("$count = $statement->rowCount();\n")
    file.write("if ($count > 0){\n")
    file.write("$json['Status'] = \"SUCCESS - Updated $count Rows\";\n")
    file.write("} else {\n")
    file.write("$json['Status'] = \"ERROR - Updated 0 Rows - Check for Valid Ids \";\n")
    file.write("}\n")
    file.write("}catch (Exception $e) { \n")
    file.write("    $json['Exception'] =  $e->getMessage();\n")
    file.write("}\n")

    file.write("$json['Action'] = $action;\n")

    file.write("}\n");
    file.write("} else if ($action == \"delete" + sql_to_camel(table) + "\"){\n")

    file.write("$sql = \"DELETE FROM " + table + " WHERE " + myresult[0][3] + " = ?\";\n")

    file.write("$args = array();\n")
    file.write("array_push($args, $" + names[0] + ");\n")
    file.write("if (!IsNullOrEmpty($" + names[0] + ")){\n")
    file.write("try{\n")
    file.write("  $statement = $conn->prepare($sql);\n")
    file.write("  $statement->execute($args);\n")
    file.write("$count = $statement->rowCount();\n")
    file.write("if ($count > 0){\n")
    file.write("$json['Status'] = \"SUCCESS - Deleted $count Rows\";\n")
    file.write("} else {\n")
    file.write("$json['Status'] = \"ERROR - Deleted 0 Rows - Check for Valid Ids \";\n")
    file.write("}\n")
    file.write("}catch (Exception $e) { \n")
    file.write("    $json['Exception'] =  $e->getMessage();\n")
    file.write("}\n")

    file.write("} else {\n")
    file.write("$json['Status'] = \"ERROR - Id is required\";\n")
    file.write("}\n")

    file.write("$json['Action'] = $action;\n")

    # generate select statment
    file.write("} else if ($action == \"get" + sql_to_camel(table) + "\"){\n")
    file.write("    $args = array();\n")

    file.write("    $sql = \"SELECT * FROM " + table + "\";\n")
    file.write(" $first = true;\n")

    for i in range(0, len(myresult)):
        file.write("if (!IsNullOrEmpty($" + names[i] + ")){\n")
        file.write("      if ($first) {\n")
        file.write("        $sql .= \" WHERE " + myresult[i][3] + " = ? \";\n")
        file.write("        $first = false;\n")
        file.write("      }else{\n")
        file.write("        $sql .= \" AND " + myresult[i][3] + " = ? \";\n")
        file.write("      }\n")
        file.write("      array_push ($args, $" + names[i] + ");\n")
        file.write("    }\n")

    file.write("    $json['SQL'] = $sql; \n")
    file.write("    try{\n")
    file.write("      $statement = $conn->prepare($sql);\n")
    file.write("      $statement->setFetchMode(PDO::FETCH_ASSOC);\n")
    file.write("      $statement->execute($args);\n")
    file.write("      $result = $statement->fetchAll();\n")
    file.write("    }catch (Exception $e) { \n")
    file.write("      $json['Exception'] =  $e->getMessage();\n")
    file.write("    }\n")
    file.write("    foreach($result as $row ) {\n")
    file.write("        $json['" + table + "'][] = $row;\n")
    file.write("    }\n")

    file.write("} else { \n")
    file.write("    $json['Exeption'] = \"Unrecognized Action \";\n")
    file.write("} \n")
    file.write("} \n")
    file.write("else{\n")
    file.write("  $json['Exeption'] = \"Invalid JSON on Inbound Request\";\n")
    file.write("} \n")
    file.write("echo json_encode($json);\n")
    file.write("$conn = null; \n")
    file.write("?>\n")
    file.close()

    # now print out the HTML for the POC Widget
    print("<h2>Maintain " + sql_to_camel(table) + "</h2>")
    print("<h3>Add/Edit " + sql_to_camel(table) + "</h3>")
    print(
        "      <p>Use this form to test adding or editing a " + table + ".  If you include a value in the " + primarykey + " id field, it will")
    print(" update that " + sql_to_camel(table) + ".  Otherwise, it will add a new one.</p>")
    print("      <form id=\"" + sql_to_camel(table) + "form\">")
    print("         <input type=\"hidden\" id=\"action\" name=\"action\" size=\"50\" value=\"addOrEdit" + sql_to_camel(table) + "\">")
    print("         <br>")

    for x in names:
        print("         <label>"+x+"</label><input type=\"text\" id=\"" + x.lower() + "\" name=\"" + x.lower() + "\" size=\"50\">")
        print("     <br>")
        print("      <br>")

    print(
        "      <button type=\"button\" onclick=\"submitJson('#" + sql_to_camel(table) + "form', '" + filename + "');\">Add/Update</button>")
    print(
        "      <button type=\"button\" onclick=\"submitJson(null, '" + filename + "', {'action':'get" + sql_to_camel(table) + "'});\">Get" + sql_to_camel(table) + "</button>")
    print(
        "     <button type=\"button\" onclick=\"submitJson(null, '" + filename + "', {'action':'get" + sql_to_camel(table) + "','" + primarykey + "':document.getElementById('" + primarykey + "').value}, load" + sql_to_camel(table) + ");\">Load " + sql_to_camel(table) + "</button>")
    print("   </form>")

    print("<script>")
    print("function load"+sql_to_camel(table)+"(maindta){")
    print("   if (maindta['"+table+"']){")
    for x in myresult:
        print("          $(\"#"+sql_to_camel(x[3]).lower()+"\").val(maindta['"+table+"'][0]."+x[3]+");")
    print("        }else{")
    print("          alert(\"unable to find a matching ID!\");")
    print("        }")
    print("    };")
    print("</script>")