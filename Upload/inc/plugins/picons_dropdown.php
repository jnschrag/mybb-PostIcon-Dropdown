<?php
/**
 * Post Icons Drop Down
 * Copyright 2013 Jacque Schrag
 */

 // Disallow Direct Access
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
*	Plugin Information
*/
function picons_dropdown_info() {
    
    return array(
        "name" => "Post Icons Drop Down",
        "description" => "Allows users to select post icons from a drop down list.",
        "website"			=> "http://github.com/jnschrag/mybb-HTML-usergroups",
		"author"			=> "Jacque Schrag",
		"authorsite"		=> "http://jacqueschrag.com",
		"version"			=> "1.0",
		"guid"				=> "",
		"compatibility"		=> "*"       
    );   
}
/*
*	End Plugin Information
*/

function picons_dropdown_activate() {
	global $db;

	$settings_group = array(
        "gid" => "",
        "name" => "picons_dropdown",
        "title" => "Post Icons",
        "description" => "Settings for the Post Icons Plugin",
        "disporder" => "0",
        "isdefault" => "0",
    );
    
    $db->insert_query("settinggroups", $settings_group);
    $gid = $db->insert_id();

    $setting[0] = array(
	    "name" => "picons_dropdown_on",
	    "title" => "Do you want the Post Icons Plugin On?",
	    "description" => "Select Yes if you would like this plugin to run.",
	    "optionscode" => "yesno",
	    "value" => "1",
	    "disporder" => "1",
	    "gid" => $gid,
	);
	foreach ($setting as $row) {
	    $db->insert_query("settings", $row);
	}
    rebuild_settings();

	$template1 = array(
		"tid" => NULL,
		"title" => "picons_dropdown_list",
		"template" => $db->escape_string('<tr>
<td class="trow1" style="vertical-align: top"><strong>{$lang->post_icon}</strong></td>
<td class="trow1" valign="top">{$iconlist}</td>
</tr>
'),
		"sid" => "-1"
	);
	$db->insert_query("templates", $template1);

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("newthread", "#".preg_quote('{$posticons}')."#i", "{$piconselect}");
	find_replace_templatesets("newreply", "#".preg_quote('{$posticons}')."#i", "{$piconselect}");
	find_replace_templatesets("editpost", "#".preg_quote('{$posticons}')."#i", "{$piconselect}");
}

// This function runs when the plugin is deactivated.
function picons_dropdown_deactivate()
{
	global $db;
	$query = $db->simple_select("settinggroups", "gid", "name='picons_dropdown'");
    $gid = $db->fetch_field($query, 'gid');
    $db->delete_query("settinggroups", "gid='".$gid."'");
    $db->delete_query("settings", "gid='".$gid."'");
    $db->delete_query("templates", "title LIKE '%picons_dropdown%'");
    rebuild_settings();

    include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("newthread", "#".preg_quote('{$piconselect}')."#i", "{$posticons}");
	find_replace_templatesets("newreply", "#".preg_quote('{$piconselect}')."#i", "{$posticons}");
	find_replace_templatesets("editpost", "#".preg_quote('{$piconselect}')."#i", "{$posticons}");
}


function get_post_icons_list()
{
	global $mybb, $cache, $icon, $theme, $templates, $lang;

	$listed = 0;
	if($mybb->input['icon'])
	{
		$icon = $mybb->input['icon'];
	}

	$no_icons_checked = " checked=\"checked\"";
	// read post icons from cache, and sort them accordingly
	$posticons_cache = $cache->read("posticons");
	$posticons = array();
	foreach($posticons_cache as $posticon)
	{
		$posticons[$posticon['name']] = $posticon;
	}
	
	$iconlist .= "<select name='icon'><option value='-1'>No Post Icon</option>";
	foreach($posticons as $dbicon)
	{
		$dbicon['path'] = htmlspecialchars_uni($dbicon['path']);
		$dbicon['name'] = htmlspecialchars_uni($dbicon['name']);

		if($icon == $dbicon['iid'])
		{
			$iconlist .= "<option value='".$dbicon['iid']."' selected>".$dbicon['name']."</option>";
			$no_icons_checked = "";
		}
		else
		{
			$iconlist .= "<option value='".$dbicon['iid']."'>".$dbicon['name']."</option>";
		}
	}
	$iconlist .= "</select>";

	eval("\$posticons = \"".$templates->get("picons_dropdown_list")."\";");

	return $posticons;
}


/*
*	Replaces dropdown list with radio button list
*/
$plugins->add_hook("newthread_end", "picons_dropdown_display");
$plugins->add_hook("newreply_end", "picons_dropdown_display");
$plugins->add_hook("editpost_end", "picons_dropdown_display");
function picons_dropdown_display() 
{
	global $mybb, $forum, $theme, $piconselect;
	
	if($mybb->settings['picons_dropdown_on'] == 1 && $forum['allowpicons'] != 0) {
		$piconselect = get_post_icons_list();
	}
	else {
		$piconselect = get_post_icons();
	}
}


?>