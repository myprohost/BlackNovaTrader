<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2012 Ron Harwood and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: admin_languages.php

include "config/config.php";

// New database driven language entries
load_languages($db, $lang, array('admin', 'common', 'global_includes', 'combat', 'footer', 'news'), $langvars, $db_logging);

updatecookie();

$title = $l->get('l_admin_title');
include "header.php";

connectdb ();
bigtitle ();

function checked($yesno)
{
    return(($yesno == "Y") ? "checked" : "");
}

function yesno($onoff)
{
    return(($onoff == "ON") ? "Y" : "N");
}

if (isset($_POST['menu']))
{
    $module = $_POST['menu'];
}

if (isset($_POST['swordfish']))
{
    $swordfish = $_POST['swordfish'];
}
else
{
    $swordfish = '';
}

if ($swordfish != $langadminpass)
{
    echo "<form action='admin_languages.php' method='post'>";
    echo "Password: <input type='password' name='swordfish' size='20' maxlength='20'>&nbsp;&nbsp;";
    echo "<input type='submit' value='Submit'><input type='reset' value='Reset'>";
    echo "</form>";
}
else
{
    if (empty($module))
    {
        echo "Welcome to the Blacknova Traders language administration module<br><br>";
        echo "select a function from the list below:<br>";
        echo "<form action='admin_languages.php' method='post'>";
        echo "<select name='menu'>";
        echo "<option value='langedit'>Edit language terms</option>";
        echo "<option value='addterm'>Add new term</option>";
        echo "</select>";
        echo "<input type='hidden' name='swordfish' value='$swordfish'>";
        echo "&nbsp;<input type='submit' value='Submit'>";
        echo "</form>";
    }
    else
    {
        $button_main = true;

		if ($module == "langedit")
        {
            echo "<strong>Language editor</strong>";
			echo "<br/>";
			echo "<form action='admin_languages.php' method='post'>";
			if (empty($selectedlanguage))
            {
                echo "<select size='20' name='selectedlanguage'>";
                $res = $db->Execute("SELECT DISTINCT (language) AS language FROM {$db->prefix}languages ORDER BY language");
                db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
                while (!$res->EOF)
                {
                    $row=$res->fields;
                    echo "<option value='$row[language]'>$row[language]</option>";
                    $res->MoveNext();
                }
                echo "</select>";
				echo "<input type='hidden' name='operation' value='selectlanguage'>";
                echo "&nbsp;<input type='submit' value='Select'>";
            }
            else
            {
                if ($operation == "selectlanguage")
                {
					echo "Language: " . $selectedlanguage;
					echo "<br /><br />";
					echo "<select size='20' name='selectedterm'>";
					$res = $db->Execute("SELECT name,value FROM {$db->prefix}languages WHERE language=?;", array($selectedlanguage));
                    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
					while (!$res->EOF)
                    {
                    $row=$res->fields;
                    echo "<option value='$row[name]'>$row[name] | $row[value]</option>";
                    $res->MoveNext();
                    }
					echo "</select>";
					echo "<input type='hidden' name=selectedlanguage value=$selectedlanguage>";
                    echo "<input type='hidden' name=operation value='editterm'>";
					echo "<br /><br />";
                    echo "<input type=submit value='EDIT'>";
				}				
				elseif ($operation == "editterm")
                {
				    echo "Language: " . $selectedlanguage;
					echo "<br />Selected Term: " . $selectedterm;
					echo "<br /><br />";
					$res = $db->Execute("SELECT * FROM {$db->prefix}languages WHERE name=? AND language=?;", array($selectedterm, $selectedlanguage));
                    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
					
					$row=$res->fields;
					echo "<br />".$row[name]."<br /><br />";
					
					echo "<input type='text' name='originalterm' disabled value='".$row['value']."'><br />";
					echo "<input type='text' name='editedterm' value='".$row['value']."'><br />";
					echo "<input type='hidden' name=termid value='".$row['lang_id']."'>";
					echo "<input type='hidden' name=selectedlanguage value=$selectedlanguage>";
					echo "<input type='hidden' name=saveterm value=$selectedterm>";
                    echo "<input type='hidden' name=operation value='saveterm'>";
					echo "<br /><br />";
                    echo "<input type=submit value='SAVE'>";
				}
				elseif ($operation == "saveterm")
                {
				    echo "Language: " . $selectedlanguage;
					echo "<br />Term ID: " . $termid;
					echo "<br />Edited Term: " . $editedterm;
                    echo "<br /><br />";
					$res = $db->Execute("UPDATE {$db->prefix}languages SET value=? WHERE lang_id=?;", array($editedterm , $termid));
                    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

                    echo "Changes saved<br><br>";
					echo "<input type='hidden' name=selectedlanguage value=$selectedlanguage>";
					echo "<input type='hidden' name=operation value='selectlanguage'>";
					echo "<br /><br />";
                    echo "<input type=submit value=\"Return to Language Editor \">";
                    $button_main = false;
				}
				else
                {
					echo "Invalid operation";
                }
			}
			
			echo "<input type='hidden' name=menu value=langedit>";
            echo "<input type='hidden' name=swordfish value=$swordfish>";
            echo "</form>";
			
        }
		elseif ($module == "addterm")
        {
            echo "<strong>Add new term</strong>";
            echo "<br>";
            echo "<form action='admin_languages.php' method='post'>";
            if (empty($addterm))
            {	
				echo "<label>NAME: <input type='text' name='name' value=''></label><br />";
				echo "<label>VALUE: <input type='text' name='value' value=''></label><br />";
				echo "<label>CATEGORY: <input type='text' name='category' value=''></label><br />";
				
			    echo "<input type='hidden' name=addterm value=addterm>";
                echo "<input type='hidden' name='operation' value='saveterm'>";
                echo "&nbsp;<input type='submit' value='ADD'>";
            }
            else
            {
                if ($operation == "saveterm")
                {
                    // Update database
				$res = $db->Execute("SELECT DISTINCT (language) AS language FROM {$db->prefix}languages ORDER BY language");
                db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
                while (!$res->EOF)
                {
                    $row=$res->fields;
                    $languages[] = $row[language];
                    $res->MoveNext();
                }
				echo "<pre>"; print_r($languages); echo "</pre>";
				
					foreach ($languages as $lang) {
						echo $lang . " | ";
                    $resx = $db->Execute("INSERT INTO {$db->prefix}languages (lang_id,language,name,value,category) VALUES (?,?,?,?,?)", array(NULL, $lang , $name, $value, $category));
                    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
					}
                    echo "Changes saved<br><br>";
                    echo "<input type=submit value=\"Return to Language Editor \">";
                    $button_main = false;
                }
                else
                {
                    echo "Invalid operation";
                }
            }

            echo "<input type='hidden' name=menu value=addterm>";
            echo "<input type='hidden' name=swordfish value=$swordfish>";
            echo "</form>";
        }
        else
        {
            echo "Unknown function";
        }

        if ($button_main)
        {
            echo "<p>";
            echo "<form action=admin_languages.php method=post>";
            echo "<input type='hidden' name=swordfish value=$swordfish>";
            echo "<input type=submit value=\"Return to main menu\">";
            echo "</form>";
        }
    }
}

include "footer.php";
?>
