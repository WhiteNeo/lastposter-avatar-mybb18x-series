<?php
/**
*@ Autor: Whiteneo
* https://www.youtube.com/watch?v=2zRv7DWpnbI 
* https://www.youtube.com/watch?v=FhDRmJXFU1o 
* https://www.youtube.com/watch?v=yO1Ed8o48i8 
* https://www.youtube.com/watch?v=NFeF8vGu5CQ
*@ Fecha: 2013-12-12
*@ Version: 2.9.x
*@ Contacto: neogeoman@gmail.com
*/

// Inhabilitar acceso directo a este archivo
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Añadir hooks
if($settings['sidebox5'] == 0 || $settings['sidebox5'] == 1)
{
	$plugins->add_hook('index_end', 'avatarep_portal_sb');	
	$plugins->add_hook('forumdisplay_end', 'avatarep_portal_sb');		
	$plugins->add_hook('showthread_end', 'avatarep_portal_sb');		
	$plugins->add_hook("portal_end", "avatarep_portal_sb");		
}
$plugins->add_hook('build_forumbits_forum', 'forumlist_avatar_fname',15);
$plugins->add_hook('forumdisplay_thread', 'forumlist_avatar_thread',15);		
$plugins->add_hook('forumdisplay_announcement', 'avatarep_announcement',15);
$plugins->add_hook("index_end", "avatarep_portal_fname",15);	
$plugins->add_hook('showthread_end', 'avatarep_threads');
$plugins->add_hook('search_results_thread', 'forumlist_avatar_search',15);
$plugins->add_hook('search_results_post', 'forumlist_avatar_search',15);
$plugins->add_hook("private_message", "avatarep_private_fname",15);
$plugins->add_hook("portal_end", "avatarep_portal_fname",15);	
$plugins->add_hook("portal_announcement", "avatarep_portal",15);	
$plugins->add_hook("usercp_end", "avatarep_usercp_fname",15);
$plugins->add_hook('global_start', 'avatarep_popup');
$plugins->add_hook('global_end', 'avatarep_style_guser',10);
$plugins->add_hook('pre_output_page', 'avatarep_style_output',10);
$plugins->add_hook('pre_output_page', 'forumlist_avatar',15);

// Informacion del plugin
function avatarep_info()
{
	global $db, $mybb, $lang, $avatarep_config_link;

    $lang->load("avatarep", false, true);
	$avatarep_config_link = '';

	if(isset($mybb->settings['avatarep_active']))
	{
		$avatarep_config_link = '<div style="float: right;"><a href="index.php?module=config&amp;action=change&amp;search=avatarep" style="color:#035488; background: url(../images/icons/brick.png) no-repeat 0px 18px; padding: 18px; text-decoration: none;"> '.$db->escape_string($lang->avatarep_config).'</a></div>';
	}
	else if($mybb->settings['avatarep_active'] == 0)
	{
		$avatarep_config_link .= '<div style="float: right; color: rgba(136, 17, 3, 1); background: url(../images/icons/exclamation.png) no-repeat 0px 18px; padding: 21px; text-decoration: none;">Plugin disabled</div>';
	}
	return array(
        "name"			=> $db->escape_string($lang->avatarep_name),
    	"description"	=> $db->escape_string($lang->avatarep_descrip) . $avatarep_config_link,
		"website"		=> "https://community.mybb.com/mods.php?action=view&pid=74",
		"author"		=> "Whiteneo",
		"authorsite"	=> "https://soportemybb.es",
		"version"		=> "2.9.8",
		"codename" 		=> "last_poster_avatar",
		"compatibility" => "18*"
	);
} 

//Se ejecuta al activar el plugin
function avatarep_activate() {
    //Variables que vamos a utilizar
   	global $mybb, $cache, $db, $lang, $templates;

    $lang->load("avatarep", false, true);

    // Crear el grupo de opciones
    $query = $db->simple_select("settinggroups", "COUNT(*) as rows");
    $rows = $db->fetch_field($query, "rows");

    $avatarep_groupconfig = array(
        'name' => 'avatarep',
        'title' => $db->escape_string($lang->avatarep_title),
        'description' => $db->escape_string($lang->avatarep_title_descrip),
        'disporder' => $rows+1,
        'isdefault' => 0
    );

    $group['gid'] = $db->insert_query("settinggroups", $avatarep_groupconfig);

    // Crear las opciones del plugin a utilizar
    $avatarep_config = array();

    $avatarep_config[] = array(
        'name' => 'avatarep_active',
        'title' => $db->escape_string($lang->avatarep_power),
        'description' => $db->escape_string($lang->avatarep_power_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 10,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_foros',
        'title' => $db->escape_string($lang->avatarep_forum),
        'description' => $db->escape_string($lang->avatarep_forum_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 20,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_temas',
        'title' => $db->escape_string($lang->avatarep_thread_owner),
        'description' => $db->escape_string($lang->avatarep_thread_owner_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 30,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_temas_mark',
        'title' =>  $db->escape_string($lang->avatarep_thread_lastposter_mark),
        'description' => $db->escape_string($lang->avatarep_thread_lastposter_mark_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 40,
        'gid' => $group['gid']
    );
	
    $avatarep_config[] = array(
        'name' => 'avatarep_contributor',
        'title' =>  $db->escape_string($lang->avatarep_thread_contributor),
        'description' => $db->escape_string($lang->avatarep_thread_contributor_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 50,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_latest_threads',
        'title' =>  $db->escape_string($lang->avatarep_latest_threads),
        'description' => $db->escape_string($lang->avatarep_latest_threads_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 60,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_private',
        'title' =>  $db->escape_string($lang->avatarep_private),
        'description' => $db->escape_string($lang->avatarep_private_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 70,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_portal',
        'title' =>  $db->escape_string($lang->avatarep_portal),
        'description' => $db->escape_string($lang->avatarep_portal_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 80,
        'gid' => $group['gid']
    );	
	
    $avatarep_config[] = array(
        'name' => 'avatarep_busqueda',
        'title' =>  $db->escape_string($lang->avatarep_search),
        'description' => $db->escape_string($lang->avatarep_search_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 90,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_menu',
        'title' =>  $db->escape_string($lang->avatarep_menu),
        'description' => $db->escape_string($lang->avatarep_menu_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 100,
        'gid' => $group['gid']
    );

	$avatarep_config[] = array(
        'name' => 'avatarep_menu_events',
        'title' =>  $db->escape_string($lang->avatarep_menu_events),
        'description' => $db->escape_string($lang->avatarep_menu_events_descrip),
        'optionscode' => 'select \n1=On Click \n2=Mouse Over',
        'value' => '1',
        'disporder' => 110,
        'gid' => $group['gid']
    );

	$avatarep_config[] = array(
        'name' => 'avatarep_guests',
        'title' =>  $db->escape_string($lang->avatarep_guests),
        'description' => $db->escape_string($lang->avatarep_guests_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 120,
        'gid' => $group['gid']
    );	
	
	$avatarep_config[] = array(
        'name' => 'avatarep_format',
        'title' =>  $db->escape_string($lang->avatarep_format),
        'description' => $db->escape_string($lang->avatarep_format_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 130,
        'gid' => $group['gid']
    );

	$avatarep_config[] = array(
        'name' => 'avatarep_onerror',
        'title' =>  $db->escape_string($lang->avatarep_onerror),
        'description' => $db->escape_string($lang->avatarep_onerror_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 140,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_version',
        'title' =>  "Version",
        'description' => "Plugin version of last poster avatar on threadlist and forumlist",
        'optionscode' => 'text',
        'value' => 298,
        'disporder' => 150,
        'gid' => 0
    );
    
    foreach($avatarep_config as $array => $content)
    {
        $db->insert_query("settings", $content);
    }

	//Adding new templates
	$templatearray = array(
		'title' => 'avatarep_popup_hover',
		'template' => $db->escape_string('<div class="modal_avatar_hover">
	<div class="thead">
	<span class="avatarep_tavatar">
			{$memprofile[\'avatar\']}
	</span>
	<span class="avatarep_usern">
			<a href="member.php?action=profile&amp;uid={$uid}">
				<span class="avatarep_uname">{$formattedname}</span>
			</a>
			<span class="avatarep_usert">
				{$usertitle}
			</span>
	</span>
	</div>
	<div class="trow2 avatarep_divisor">
		<div class="avatarep_uprofile">
			<span class="avatarep_memprofile">
				<a href="member.php?action=profile&amp;uid={$uid}">{$lang->avatarep_user_profile}</a>
				<a href="private.php?action=send&amp;uid={$memprofile[\'uid\']}">{$lang->avatarep_user_sendpm}</a>
				<a href="search.php?action=finduserthreads&amp;uid={$uid}">{$lang->find_threads}</a>
				<a href="search.php?action=finduser&amp;uid={$uid}">{$lang->find_posts}</a>
			</span>
			<span class="avatarep_data">
				<span class="avatarep_data_item">{$lang->registration_date} {$memregdate}</span>
				<span class="avatarep_data_item">{$lang->reputation} {$memprofile[\'reputation\']}</span>
				<span class="avatarep_data_item">{$lang->total_threads} {$memprofile[\'threadnum\']}</span>
				<span class="avatarep_data_item">{$lang->total_posts} {$memprofile[\'postnum\']}</span>
				<span class="avatarep_data_item">{$lang->lastvisit} {$memlastvisitdate} {$memlastvisittime}</span>
				<span class="avatarep_data_item">{$lang->warning_level} <a href="{$warning_link}">{$warning_level} %</a></span>
				<span style="float:right;">{$lang->postbit_status} {$online_status}</span>	
			</span>
		</div>
	</div>
</div>'),
		'sid' => '-1',
		'version' => '1803',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		'title' => 'avatarep_popup_error_hover',
		'template' => $db->escape_string('<div class="modal_avatar_hover">
	<div class="thead"><img src="images/error.png" alt="Avatarep Error" />{$lang->avatarep_user_error}</div>
	<div class="trow1" style="padding: 10px;text-align:center;">{$lang->avatarep_user_error_text}</div>
</div>'),
		'sid' => '-1',
		'version' => '1803',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);

		$templatearray = array(
		'title' => 'avatarep_popup',
		'template' => $db->escape_string('<div class="modal">
	<div class="thead">
	<span class="avatarep_tavatar">
			{$memprofile[\'avatar\']}
	</span>
	<span class="avatarep_usern">
			<a href="member.php?action=profile&amp;uid={$uid}">
				<span class="avatarep_uname">{$formattedname}</span>
			</a>
			<span class="avatarep_usert">
				{$usertitle}
			</span>
	</span>
	</div>
	<div class="trow2 avatarep_divisor">
		<div class="avatarep_uprofile">
			<span class="avatarep_memprofile">
				<a href="member.php?action=profile&amp;uid={$uid}">{$lang->avatarep_user_profile}</a>
				<a href="private.php?action=send&amp;uid={$memprofile[\'uid\']}">{$lang->avatarep_user_sendpm}</a>
				<a href="search.php?action=finduserthreads&amp;uid={$uid}">{$lang->find_threads}</a>
				<a href="search.php?action=finduser&amp;uid={$uid}">{$lang->find_posts}</a>
			</span>
			<span class="avatarep_data">
				<span class="avatarep_data_item">{$lang->registration_date} {$memregdate}</span>
				<span class="avatarep_data_item">{$lang->reputation} {$memprofile[\'reputation\']}</span>
				<span class="avatarep_data_item">{$lang->total_threads} {$memprofile[\'threadnum\']}</span>
				<span class="avatarep_data_item">{$lang->total_posts} {$memprofile[\'postnum\']}</span>
				<span class="avatarep_data_item">{$lang->lastvisit} {$memlastvisitdate} {$memlastvisittime}</span>
				<span class="avatarep_data_item">{$lang->warning_level} <a href="{$warning_link}">{$warning_level} %</a></span>
				<span style="float:right;">{$lang->postbit_status} {$online_status}</span>	
			</span>
		</div>
	</div>
</div>'),
		'sid' => '-1',
		'version' => '1803',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		'title' => 'avatarep_popup_error',
		'template' => $db->escape_string('<div class="modal">
	<div class="thead"><img src="images/error.png" alt="Avatarep Error" />{$lang->avatarep_user_error}</div>
	<div class="trow1" style="padding: 10px;text-align:center;">{$lang->avatarep_user_error_text}</div>
</div>'),
		'sid' => '-1',
		'version' => '1803',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);
	// Añadir el css para la tipsy
	$avatarep_css = '.modal_avatar{display: none;width: auto;height: auto;position: absolute;z-index: 99999;}
.modal_avatar_hover{width: 220px;height: auto;position: absolute;z-index: 99999;}
.avatarep_tavatar {padding: 0px 5px;}
.avatarep_tavatar img {height: 80px;width: 80px;padding: 5px;border-radius: 50%;}
.avatarep_usern{float: right;right: 10px;position: absolute;margin-top: -60px;font-size: 15px;background: #f5fdff;padding: 10px;opacity: 0.5;color: #424242;border-radius:2px;}
.avatarep_online {background: #008000;box-shadow: 1px 1px 2px 1px rgba(14, 252, 14, 0.8);border-radius: 50%;height: 90px;width: 90px;margin-left: 10px;opacity: 0.9;}
.avatarep_offline{background: #FFA500;box-shadow: 1px 1px 2px 1px rgba(252, 165, 14, 0.8);border-radius: 50%;height: 90px;width: 90px;margin-left: 10px;opacity: 0.9;}
.avatarep_divisor{margin-top: -60px;}
.avatarep_profile{vertical-align: top;padding-left: 9px;width:340px;color:#424242;}
.avatarep_profile a{color: #051517;}
.avatarep_profile a:hover{color: #e09c09;}
.avatarep_uprofile{line-height:1.5;margin-top: 40px;padding: 10px;}
.avatarep_uname{font-size:15px;}
.avatarep_memprofile{font-size:11px;font-weight:bold;}
.avatarep_memprofile a{display: inline-block;padding: 0px 10px 15px 10px;}
.avatarep_data{font-size: 11px;}
.avatarep_data_item{display:block;}
.avatarep_status{display:block;}
.avatarep_img_contributor{padding: 2px;border: 1px solid #D8DFEA;width: 20px;height: 20px;border-radius: 50%;opacity: 0.9;	margin: 2px 5px 0px 2px;float: left;}
.avatarep_img{padding: 3px;border: 1px solid #D8DFEA;width: 30px;height: 30px;border-radius: 50%;opacity: 0.9;margin: 0px 5px 0px 2px;}
.avatarep_fd{float:left;margin: auto;padding: 0px 20px 0px 0px;width:30px;height:40px}
.avatarep_fda,.avatarep_fdl,.avatarep_fdan,.avatarep_fda_mine,.avatarep_fdl_mine{float:left}
.avatarep_fda,.avatarep_fda_mine{margin-right:15px}
.avatarep_fdl_img{width: 20px;height: 20px;border-radius: 50px;position: absolute;margin-left: -35px;margin-top: 25px;border: 1px solid #424242;padding: 2px;}
@media screen and (max-width: 450px){
.avatarep_memprofile a{display: block;padding: 2px;}
.avatarep_online, .avatarep_offline{height: 35px;width: 35px;}	
.avatarep_tavatar img {height: 30px;width: 30px;padding: 2px;}	
.avatarep_divisor{margin-top: -28px;}
.avatarep_uname{font-size:12px;}
.avatarep_uprofile{margin-top: 0px;padding: 5px;}
.avatarep_usern{float: right;right: 3px;position: absolute;margin-top: -30px;font-size: 12px;background: #f5fdff;padding: 5px;opacity: 0.5;color: #424242;border-radius:2px;}
.avatarep_img_contributor{padding: 2px;border: 1px solid #D8DFEA;width: 19px;height: 19px;border-radius: 50%;opacity: 0.9;	margin: 2px 5px 0px 2px;float: left;}
.avatarep_img{padding: 2px;border: 1px solid #D8DFEA;width: 19px;height: 19px;border-radius: 50%;opacity: 0.9;margin: 0px 5px 0px 2px;}
.avatarep_fd{float:left;margin: auto;padding: 0px 10px 0px 0px;width:20px;height:20px}
.avatarep_fda,.avatarep_fdl,.avatarep_fdan,.avatarep_fda_mine,.avatarep_fdl_mine{float:left}
.avatarep_fda,.avatarep_fda_mine{margin-right:15px}
.avatarep_fdl_img{width: 20px;height: 20px;border-radius: 50px;position: absolute;margin-left: -35px;margin-top: 25px;border: 1px solid #424242;padding: 2px;}
}';

	$stylesheet = array(
		"name"			=> "avatarep.css",
		"tid"			=> 1,
		"attachedto"	=> 0,		
		"stylesheet"	=> $db->escape_string($avatarep_css),
		"cachefile"		=> "avatarep.css",
		"lastmodified"	=> TIME_NOW
	);

	$sid = $db->insert_query("themestylesheets", $stylesheet);
	
	//Archivo requerido para cambios en estilos y plantillas.
	require_once MYBB_ADMIN_DIR.'/inc/functions_themes.php';
	cache_stylesheet($stylesheet['tid'], $stylesheet['cachefile'], $avatarep_css);
	update_theme_stylesheet_list(1, false, true);

    //Archivo requerido para reemplazo de templates
    require_once '../inc/adminfunctions_templates.php';
	
    // Reemplazos que vamos a hacer en las plantillas 1.- Platilla 2.- Contenido a Reemplazar 3.- Contenido que reemplaza lo anterior
	find_replace_templatesets("headerinclude",'#'.preg_quote('{$stylesheets}').'#', '{$avatarep_script}{$stylesheets}');		
    find_replace_templatesets("forumbit_depth1_forum_lastpost", '#^(.*)$#s', '<avatarep_uid_[{$lastpost_data[\'lastposteruid\']}]>' . "\\1");	
    find_replace_templatesets("forumbit_depth2_forum_lastpost", '#^(.*)$#s', '<avatarep_uid_[{$lastpost_data[\'lastposteruid\']}]>' . "\\1");
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$attachment_count}').'#', '<avatarep_uid_[{$thread[\'uid\']}]>{$thread[\'avatarep\']}{$attachment_count}');
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$lastpostdate}').'#', '<avatarep_uid_[{$thread[\'lastposteruid\']}]>{$lastpostdate}');	   
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$lastposterlink}').'#', '{$avbr}{$lastposterlink}');	
	find_replace_templatesets("forumdisplay_announcements_announcement", '#'.preg_quote('<td class="{$bgcolor} forumdisplay_announcement">').'#', '<td class="{$bgcolor} forumdisplay_announcement"><avatarep_uid_[{$announcement[\'uid\']}]>');	
    find_replace_templatesets("search_results_threads_thread", '#'.preg_quote('{$attachment_count}').'#', '<avatarep_uid_[{$thread[\'uid\']}]>{$attachment_count}');
    find_replace_templatesets("search_results_threads_thread", '#'.preg_quote('{$lastpostdate}').'#', '<avatarep_uid_[{$thread[\'lastposteruid\']}]>{$lastpostdate}');
    find_replace_templatesets("search_results_threads_thread", '#'.preg_quote('{$thread[\'profilelink\']}').'#', '{$avbr}{$thread[\'profilelink\']}');	
    find_replace_templatesets("search_results_posts_post", '#'.preg_quote('{$post[\'profilelink\']}').'#', '<avatarep_uid_[{$post[\'uid\']}]>{$post[\'profilelink\']}');
    find_replace_templatesets("private_messagebit", '#'.preg_quote('{$tofromusername}').'#', '<avatarep_uid_[{$tofromuid}]>{$tofromusername}');
    find_replace_templatesets("private_tracking_readmessage", '#'.preg_quote('{$readmessage[\'profilelink\']}').'#', '<avatarep_uid_[{$readmessage[\'toid\']}]>{$readmessage[\'profilelink\']}');
    find_replace_templatesets("private_tracking_unreadmessage", '#'.preg_quote('{$unreadmessage[\'profilelink\']}').'#', '<avatarep_uid_[{$unreadmessage[\'toid\']}]>{$unreadmessage[\'profilelink\']}');
	find_replace_templatesets("portal_latestthreads_thread", '#'.preg_quote('<strong><a href="').'#', '<strong><avatarep_uid_[{$thread[\'lastposteruid\']}]><a href="');
	find_replace_templatesets("showthread", '#'.preg_quote('{$thread[\'threadprefix\']}').'#', '{$avatarep_thread}{$thread[\'threadprefix\']}');
	find_replace_templatesets("usercp_latest_subscribed_threads", '#'.preg_quote('{$lastpostdate}').'#', '<avatarep_uid_[{$thread[\'lastposteruid\']}]>{$lastpostdate}');
	find_replace_templatesets("usercp_latest_threads_threads", '#'.preg_quote('{$lastpostdate}').'#', '<avatarep_uid_[{$thread[\'lastposteruid\']}]>{$lastpostdate}');	
	
	//Se actualiza la info de las plantillas
	$cache->update_forums();
	rebuild_settings();
}

function avatarep_deactivate() {
    //Variables que vamos a utilizar
	global $mybb, $cache, $db;
    // Borrar el grupo de opciones
	$db->delete_query("settings", "name LIKE('avatarep_%')");
	$db->delete_query("settinggroups", "name='avatarep'");
	$db->delete_query("templates", "title LIKE('avatarep_%')");
    //Eliminamos la hoja de estilo creada...
   	$db->delete_query('themestylesheets', "name='avatarep.css'");
	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	}

    //Archivo requerido para reemplazo de templates
 	require_once '../inc/adminfunctions_templates.php';
	
    //Reemplazos que vamos a hacer en las plantillas 1.- Platilla 2.- Contenido a Reemplazar 3.- Contenido que reemplaza lo anterior
	find_replace_templatesets("headerinclude", '#'.preg_quote('{$avatarep_script}').'#', '', 0);
    find_replace_templatesets("forumbit_depth1_forum_lastpost", '#'.preg_quote('<avatarep_uid_[{$lastpost_data[\'lastposteruid\']}]>').'#', '', 0);		
    find_replace_templatesets("forumbit_depth2_forum_lastpost", '#'.preg_quote('<avatarep_uid_[{$lastpost_data[\'lastposteruid\']}]>').'#', '', 0);	    
	find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$thread[\'avatarep\']}').'#', '',0);
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('<avatarep_uid_[{$thread[\'uid\']}]>').'#', '',0);	
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('<avatarep_uid_[{$thread[\'lastposteruid\']}]>').'#', '',0);
    find_replace_templatesets("forumdisplay_thread", '#'.preg_quote('{$avbr}').'#', '',0);		
    find_replace_templatesets("forumdisplay_announcements_announcement", '#'.preg_quote('<avatarep_uid_[{$announcement[\'uid\']}]>').'#', '',0);
    find_replace_templatesets("search_results_threads_thread", '#'.preg_quote('<avatarep_uid_[{$thread[\'uid\']}]>').'#', '',0);
    find_replace_templatesets("search_results_threads_thread", '#'.preg_quote('{$avbr}').'#', '',0);	
    find_replace_templatesets("search_results_threads_thread", '#'.preg_quote('<avatarep_uid_[{$thread[\'lastposteruid\']}]>').'#', '',0);
    find_replace_templatesets("search_results_posts_post", '#'.preg_quote('<avatarep_uid_[{$post[\'uid\']}]>').'#', '',0);
    find_replace_templatesets("private_messagebit", '#'.preg_quote('<avatarep_uid_[{$tofromuid}]>').'#', '',0);
    find_replace_templatesets("private_tracking_readmessage", '#'.preg_quote('<avatarep_uid_[{$readmessage[\'toid\']}]').'#', '',0);
    find_replace_templatesets("private_tracking_unreadmessage", '#'.preg_quote('<avatarep_uid_[{$unreadmessage[\'toid\']}]>').'#', '',0);
	find_replace_templatesets("portal_latestthreads_thread", '#'.preg_quote('<avatarep_uid_[{$thread[\'lastposteruid\']}]>').'#', '',0);
	find_replace_templatesets("showthread", '#'.preg_quote('{$avatarep_thread}').'#', '',0);	
	find_replace_templatesets("usercp_latest_subscribed_threads", '#'.preg_quote('<avatarep_uid_[{$thread[\'lastposteruid\']}]>').'#', '',0);
	find_replace_templatesets("usercp_latest_threads_threads", '#'.preg_quote('<avatarep_uid_[{$thread[\'lastposteruid\']}]>').'#', '',0);	
		
	//Se actualiza la info de las plantillas
    $cache->update_forums();
    rebuild_settings();
}

// Creamos el formato que llevara el avatar al ser llamado...
function avatarep_format_avatar($user)
{
	global $mybb, $avatar;
		$size = 2048;
		$dimensions = "30px";
		$avatar = format_avatar($user['avatar'], $dimensions, $size);
		$avatar = htmlspecialchars_uni($avatar['image']);

		if(THIS_SCRIPT == "showthread.php"){
			if($user['avatartype'] == "upload"){
				$avatar = $mybb->settings['bburl'] . "/" . $user['avatar'];
			}
			else if($user['avatartype'] == "gallery"){
				//UPDATE `miforo_users` set avatar = REPLACE(avatar, './uploads/', 'uploads/');
				$avatar = $mybb->settings['bburl'] . "/" . $user['avatar'];
			}
			else if($user['avatartype'] == "remote"){
				$avatar = $user['avatar'];
			}
			else if($user['avatartype'] == "" && $user['avatar']){
				$avatar = $mybb->settings['bburl'] . "/images/default_avatar.png";
			}	  
			else{
				$avatar = $mybb->settings['bburl'] . "/images/default_avatar.png";
			}	
		}
		if($mybb->settings['avatarep_onerror'] == 1)
			$onerror = " onerror=\"this.src=\'images/default_avatar.png\'\"";
		else
			$onerror = "";
		return array(
			'avatar' => $avatar,
			'avatarep' => '<img src="' . $avatar . '" class="avatarep_img" alt="'.htmlspecialchars_uni($user['userusername']).'"'.$onerror.' />',
			'username' => htmlspecialchars_uni($user['userusername']),
			'profilelink' => get_profile_link($user['uid']),
			'uid' => (int)$user['uid'],
			'usergroup' => (int)$user['usergroup'],
			'displaygroup' => (int)$user['displaygroup']
		);

	return format_avatar($user);
}		
// Avatar en foros
function forumlist_avatar_fname(&$forum)
{
	global $mybb, $lang, $cache;

    // Cargamos idioma
    $lang->load("avatarep", false, true);
    
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_foros'] == 0)
    {
		return false;	
	}

	if($mybb->settings['avatarep_format'] == 1)
	{
		if($forum['lastposteruid']>0)
		{
			$cache->cache['users'][$forum['lastposteruid']] = $forum['lastposter'];		
			$forum['lastposter'] = "#{$forum['lastposter']}{$forum['lastposteruid']}#";			
		}
		else
		{
			if(empty($forum['lastposter']))
				$forum['lastposter'] = $lang->guest;			
			$cache->cache['guests'][] = $forum['lastposter'];		
			$forum['lastposter'] = "#{$forum['lastposter']}#";					
		}
	}
	else
	{
		$forum['lastposter'] = htmlspecialchars_uni($forum['lastposter']);		
	}
}

function forumlist_avatar(&$content)
{
	global $cache, $db, $fcache, $mybb, $lang, $avatar_events;

    // Cargamos idioma
    $lang->load("avatarep", false, true);
    $show_avatars = false;
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0)
    {
		return false;	
	}
	switch(THIS_SCRIPT)
	{		
		case "index.php":if($mybb->settings['avatarep_foros'] == 1)$show_avatars = true;break;		
		case "forumdisplay.php":if($mybb->settings['avatarep_latest_threads'] == 1)$show_avatars = true;break;
		case "showthread.php":if($mybb->settings['avatarep_latest_threads'] == 1)$show_avatars = true;break;
		case "search.php":if($mybb->settings['avatarep_busqueda'] == 1)$show_avatars = true;break;
		case "portal.php":if($mybb->settings['avatarep_portal'] == 1)$show_avatars = true;break;
		case "private.php":if($mybb->settings['avatarep_private'] == 1)$show_avatars = true;break;	
		case "usercp.php":if($mybb->settings['avatarep_temas'] == 1)$show_avatars = true;break;
		default: $show_avatars=false;
	}
	if($show_avatars == true)
	{
		$avatars= array();
		if(preg_match_all('#<avatarep_uid_\[([0-9]+)\]>#', $content, $matches))
		{
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $avatar)
				{
					if(!(int)$avatar) continue;
					$avatars[] = (int)$avatar;
				}
			}		
		}		
		if(!empty($avatars))
		{
			$users = array();
			foreach($avatars as $key => $val)
			{
				$users[] = (int)$val;
			}
			$sql = implode(',', array_unique($users));
			$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, avatartype, usergroup, displaygroup', "uid IN ({$sql})");
			$find = $replace = array();				
			while($user = $db->fetch_array($query))
			{	
				if($mybb->settings['avatarep_onerror'] == 1)
					$onerror = " onerror=\"this.src=\'images/default_avatar.png\'\"";
				else
					$onerror = "";		
				$avatar = avatarep_format_avatar($user);
				if(!empty($user['avatar']))
					$user['avatar'] = "<img src=\"{$user['avatar']}\" alt=\"{$user['username']}\" class=\"avatarep_img\"{$onerror} />";
				else
					$user['avatar'] = "<img src=\"images/default_avatar.png\" alt=\"{$user['username']}\" class=\"avatarep_img\" />";
				$uid = (int)$user['uid'];

				$user['avatarep_title'] = $lang->sprintf($lang->avatarep_user_alt_forums, htmlspecialchars_uni($user['username']));
				if($mybb->settings['avatarep_menu'] == 1){
					if(function_exists("google_seo_url_profile"))
					{
						if($mybb->settings['avatarep_menu_events'] == 2)
						{		
							$user['avatar'] = "<a href=\"" . $avatar['profilelink'] . "?action=avatarep_popup\" title=\"".$user['avatarep_title']."\" class=\"forum_member{$uid}\" onclick=\"return false;\">".$user['avatar']."</a>".avatarep_hover_extends($uid,"forum_member");
						}
						else
						{
							$user['avatar'] = "<a href=\"javascript:void(0)\" title=\"".$user['avatarep_title']."\" onclick=\"MyBB.popupWindow('". $avatar['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";		
						}
					}
					else
					{
						if($mybb->settings['avatarep_menu_events'] == 2)
						{		
							$user['avatar'] = "<a href=\"member.php?uid={$uid}&amp;action=avatarep_popup\" class=\"forum_member{$uid}\" title=\"".$user['avatarep_title']."\" onclick=\"return false;\">".$user['avatar']."</a>".avatarep_hover_extends($myid,"forum_member");
						}
						else
						{	
							$user['avatar'] = "<a href=\"javascript:void(0)\" title=\"".$user['avatarep_title']."\" onclick=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";
						}
					}
				}else{
					$user['avatar'] = "<a href=\"". $avatar['profilelink'] . "\" title=\"".$user['avatarep_title']."\">".$user['avatar']."</a>";
				}	
				if($mybb->settings['avatarep_guests'] == 1 && $user['uid'] === NULL)
				{
					$user['avatarep_alt'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($user['username']));	
					$user['avatar'] = '<img class="avatarep_img" src="images/default_avatar.png" alt="'.$user['avatarep_alt'].'" />';
				}
				if($mybb->settings['avatarep_format'] == 1)
				{
					if($mybb->version_code >= 1808)
					{
						$cache->cache['users'][$user['uid']] = $user['username'];
						$user['username'] = "#{$user['username']}{$user['uid']}#";
					}
					else
					{
						$username = format_name($user['username'], $user['usergroup'], $user['displaygroup']);	
						$_f['lastposterav'] = $username;
						$user['username'] = build_profile_link($_f['lastposterav'], $user['uid']);				
					}
				}
				$user['avatar'] = '<div class="avatarep_fd">' . $user['avatar'] . '</div>';			
				foreach($avatar as $f => $r)
				{
					$find[] = "<avatarep_uid_[{$user['uid']}]>";
					$replace[] = $user['avatar'];
					if($mybb->settings['avatarep_guests'] == 1)
					{
						$find[] = "<avatarep_uid_[0]>";			
						$replace[] = "<div class=\"avatarep_fd\"><img class=\"avatarep_img\" src=\"images/default_avatar.png\" alt=\"Guest\" /></div>";				
					}				
				}
			}
			$content = str_replace($find, $replace, $content);
		}
	}
}
// Avatar en foros
function forumlist_avatar_thread()
{
	global $db, $mybb, $lang, $cache, $thread, $post, $avbr;

    // Cargamos idioma
    $lang->load("avatarep", false, true);
    $avbr = "<br />";
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0  || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_temas'] == 0)
    {
		return false;	
	}
	if($mybb->settings['avatarep_temas_mark'] == 1)
	{
		$mybb->user['username'] = htmlspecialchars_uni($mybb->user['username']);
		if(empty($mybb->user['avatar']))
			$mybb->user['avatar'] = "images/default_avatar.png";
		else
			$mybb->user['avatar'] = htmlspecialchars_uni($mybb->user['avatar']);
		if($mybb->settings['avatarep_onerror'] == 1)
			$onerror = " onerror=\"this.src=\'images/default_avatar.png\'\"";
		else
			$onerror = "";		
		if($thread['lastposteruid'] == $mybb->user['uid'] || $thread['uid'] == $mybb->user['uid'])
		{
			$thread['avatarep'] = '<div class="avatarep_fdl_mine"><img src="' . $mybb->user['avatar'] . '" alt="' . $mybb->user['username'] . '" class="avatarep_fdl_img"'.$onerror.' /></div>';			
		}
		else if($thread['lastposteruid'] != $mybb->user['uid'] && $thread['uid'] != $mybb->user['uid'] && $mybb->user['uid'])
		{
			$tid = (int)$thread['tid'];
			$tid = $db->escape_string($tid);
			$uid = (int)$mybb->user['uid'];
			$uid = $db->escape_string($uid);
			$query = $db->simple_select("posts","uid","tid={$tid} AND uid= {$uid}",array("limit"=>1));
			$is_avatar = $db->num_rows($query);
			if($is_avatar >= 1)
			{
				$thread['avatarep'] = '<div class="avatarep_fdl_mine"><img src="' . $mybb->user['avatar'] . '" alt="' . $mybb->user['username'] . '" class="avatarep_fdl_img"'.$onerror.' /></div>';			
			}
		}
		else
		{
			$thread['avatarep'] = "";
		}			
	}
	else
	{
		$thread['avatarep'] = "";
	}
	if($mybb->settings['avatarep_format'] == 1 && $mybb->settings['avatarep_temas'] == 1)
	{
		if($thread['uid']>0)
		{
			$cache->cache['users'][$thread['uid']] = $thread['username'];		
			$thread['username'] = "#{$thread['username']}{$thread['uid']}#";			
		}
		else
		{
			if(empty($thread['username']))
				$thread['username'] = $lang->guest;			
			$cache->cache['guests'][] = $thread['username'];		
			$thread['username'] = "#{$thread['username']}#";			
		}
		if($thread['lastposteruid']>0)
		{
			$cache->cache['users'][$thread['lastposteruid']] = $thread['lastposter'];		
			$thread['lastposter'] = "#{$thread['lastposter']}{$thread['lastposteruid']}#";			
		}
		else
		{
			if(empty($thread['lastposter']))
				$thread['lastposter'] = $lang->guest;
			$cache->cache['guests'][] = $thread['lastposter'];
			$thread['lastposter'] = "#{$thread['lastposter']}#";
		}
	}
	else
	{
		$thread['username'] = htmlspecialchars_uni($thread['username']);
		$thread['lastposter']= htmlspecialchars_uni($thread['lastposter']);	
	}
}
// Avatar en foros
function forumlist_avatar_search()
{
	global $mybb, $lang, $cache, $thread, $post, $lastposterlink, $avbr;

    // Cargamos idioma
    $lang->load("avatarep", false, true);
    $avbr = "<br />";
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_busqueda'] == 0)
    {
		return false;	
	}

	if($mybb->settings['avatarep_format'] == 1  && $mybb->settings['avatarep_temas'] == 1)
	{
		if($post['uid']>0)
		{
			$post['username'] = htmlspecialchars_uni($post['username']);
			$cache->cache['users'][$post['uid']] = $post['username'];		
			$post['username'] = "#{$post['username']}{$post['uid']}#";
			$post['profilelink'] = build_profile_link($post['username'], $post['uid']);			
		}
		else
		{
			if(empty($post['username']))
				$post['username'] = $lang->guest;			
			$post['username'] = htmlspecialchars_uni($post['username']);
			$cache->cache['guests'][] = $post['username'];		
			$post['username'] = "#{$post['username']}#";			
		}
		if($thread['uid']>0)
		{
			$thread['username'] = htmlspecialchars_uni($thread['username']);
			$cache->cache['users'][$thread['uid']] = $thread['username'];		
			$thread['username'] = "#{$thread['username']}{$thread['uid']}#";			
			$thread['profilelink'] = build_profile_link($thread['username'], $thread['uid']);			
		}
		else
		{
			if(empty($thread['username']))
				$thread['username'] = $lang->guest;			
			$thread['username'] = htmlspecialchars_uni($thread['username']);
			$cache->cache['guests'][] = $thread['username'];		
			$thread['username'] = "#{$thread['username']}#";			
		}
		if($thread['lastposteruid']>0)
		{	
			$thread['lastposter'] = htmlspecialchars_uni($thread['lastposter']);
			$cache->cache['users'][$thread['lastposteruid']] = $thread['lastposter'];		
			$thread['lastposter'] = "#{$thread['lastposter']}{$thread['lastposteruid']}#";
			$lastposterlink = build_profile_link($thread['lastposter'], $thread['lastposteruid']);
		}
		else
		{
			if(empty($thread['lastposter']))
				$thread['lastposter'] = $lang->guest;			
			$thread['lastposter'] = htmlspecialchars_uni($thread['lastposter']);
			$cache->cache['guests'][] = $thread['lastposter'];		
			$thread['lastposter'] = "#{$thread['lastposter']}#";			
		}
	}
	else
	{
		$post['username'] = htmlspecialchars_uni($post['username']);
		$thread['username'] = htmlspecialchars_uni($thread['username']);
		$thread['lastposter'] = htmlspecialchars_uni($thread['lastposter']);
	}
}
// Avatar en anuncions
function avatarep_announcement()
{
	global $announcement, $cache, $anno_avatar, $mybb, $lang, $avatar_events;

	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_temas'] == 0)
    {
        return false;
    }
	
    $lang->load("avatarep", false, true); 

	if($mybb->settings['avatarep_format'] == 1)
	{
		if($announcement['uid']>0)
		{
			$cache->cache['users'][$announcement['uid']] = $announcement['username'];
			$announcement['username'] = "#{$announcement['username']}{$announcement['uid']}#";
			$announcement['profilelink'] = build_profile_link($announcement['username'], $announcement['uid']);
		}
		else
		{
			if(empty($announcement['username']))
				$announcement['username'] = $lang->guest;
			$cache->cache['guests'][] = $announcement['username'];
			$announcement['username'] = "#{$announcement['username']}#";
		}
	}
	else
	{
		$announcement['username'] = htmlspecialchars_uni($announcement['username']);
	}
}

function avatarep_private_fname()
{
	global $mybb, $cache, $message, $tofromusername;
	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_private'] == 0)
    {
        return false;
    }
	if($mybb->input['fid'] == 2)
	{
		$tofromuid = (int)$message['toid'];
		$tofromusername = htmlspecialchars_uni($message['tousername']);		
	}
	else if($mybb->input['fid'] == 3)
	{
		$tofromuid = (int)$message['toid'];
		$tofromusername = htmlspecialchars_uni($message['tousername']);		
	}	
	else
	{
		$tofromuid = (int)$message['fromid'];
		$tofromusername = htmlspecialchars_uni($message['fromusername']);
	}
	if($mybb->settings['avatarep_format'] == 1)
	{
		if($tofromuid>0)
		{
			$cache->cache['users'][$tofromuid] = $tofromusername;
			$tofromusername = "#{$tofromusername}{$tofromuid}#";
		}
		else
		{		
			$cache->cache['guests'][] = $tofromusername;
			$tofromusername = "#{$tofromusername}#";
		}
	}	
	$tofromusername = build_profile_link($tofromusername, $tofromuid);
}

function avatarep_usercp_fname()
{
	global $db, $lang, $mybb, $latest_threads, $latest_subscribed;
	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_temas'] == 0)
    {
        return false;
    }
	$lang->load('avatarep',false,true);
	if(isset($latest_threads))
	{
		$users = array();
		foreach(array($latest_threads) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatarep_uid_\[([0-9]+)\]#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $user)
				{
					if(!intval($user)) continue;
					$users[] = intval($user);
				}
			}
		}
		if(!empty($users))
		{
			$sql = implode(',', $users);
			$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup, displaygroup', "uid IN ({$sql})");
			$find = $replace = array();		
			while($user = $db->fetch_array($query))
			{	
				if($mybb->settings['avatarep_format'] == 1)
				{
					$find[] = ">".$user['userusername']."<";				
					$replace[] = " style=\"display:block\">".format_name($user['userusername'],$user['usergroup'],$user['displaygroup'])."<";
				}
			}
			if(isset($latest_threads)) $latest_threads = str_replace($find, $replace, $latest_threads);
		}		
	}
	
	if(isset($latest_subscribed))
	{
		$users = array();
		foreach(array($latest_subscribed) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatarep_uid_\[([0-9]+)\]#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $user)
				{
					if(!intval($user)) continue;
					$users[] = intval($user);
				}
			}
		}
		if(!empty($users))
		{
			$sql = implode(',', $users);
			$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup, displaygroup', "uid IN ({$sql})");
			$find = $replace = array();		
			while($user = $db->fetch_array($query))
			{	
				if($mybb->settings['avatarep_format'] == 1)
				{
					$find[] = ">".$user['userusername']."<";				
					$replace[] = " style=\"display:block\">".format_name($user['userusername'],$user['usergroup'],$user['displaygroup'])."<";
				}
			}
			if(isset($latest_subscribed)) $latest_subscribed = str_replace($find, $replace, $latest_subscribed);
		}		
	}	
}

function avatarep_portal()
{
    global $mybb, $announcement, $profilelink;
	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_portal'] == 0)
    {
        return false;
    }
	if($mybb->settings['avatarep_format'] == 1)
	{
		$user = get_user($announcement['uid']);
		$link = get_profile_link($user['uid']);	
		$user['username'] = htmlspecialchars_uni($user['username']);
		$profilelink = format_name($user['username'],$user['usergroup'],$user['displaygroup']);	
		$profilelink = '<a href="'.$link.'">' . $profilelink . '</a>';
	}
}

function avatarep_portal_fname()
{
	global $db, $mybb, $lang, $cache, $latestthreads;

	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_portal'] == 0)
    {
        return false;
    }
	$lang->load('avatarep',false,true);
	$users = array();
	foreach(array($latestthreads) as $content)
	{
		if(!$content) continue;
		preg_match_all('#<avatarep_uid_\[([0-9]+)\]#', $content, $matches);
		if(is_array($matches[1]) && !empty($matches[1]))
		{
			foreach($matches[1] as $user)
			{
				if(!intval($user)) continue;
				$users[] = intval($user);
			}
		}
	}
	if(!empty($users))
	{
		$sql = implode(',', $users);
		$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup, displaygroup', "uid IN ({$sql})");
		$find = $replace = array();		
		while($user = $db->fetch_array($query))
		{	
			if($mybb->settings['avatarep_format'] == 1)
			{
				$find[] = ">".$user['userusername']."<";				
				$replace[] = " style=\"display:block;\">".format_name($user['userusername'],$user['usergroup'],$user['displaygroup'])."<";
			}
		}
		if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);
	}
}

function avatarep_portal_sb()
{
	global $db, $mybb, $lang, $cache, $sblatestthreads;

	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_latest_threads'] == 0)
    {
        return false;
    }
	$lang->load('avatarep',false,true);
	$users = array();
	foreach(array($sblatestthreads) as $content)
	{
		if(!$content) continue;
		preg_match_all('#<avatarep_uid_\[([0-9]+)\]#', $content, $matches);
		if(is_array($matches[1]) && !empty($matches[1]))
		{
			foreach($matches[1] as $user)
			{
				if(!intval($user)) continue;
				$users[] = intval($user);
			}
		}
	}
	if(!empty($users))
	{
		$sql = implode(',', $users);
		$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup, displaygroup', "uid IN ({$sql})");
		$find = $replace = array();		
		while($user = $db->fetch_array($query))
		{	
			if($mybb->settings['avatarep_format'] == 1)
			{
				$find[] = ">".$user['userusername']."<";				
				$replace[] = ">".format_name($user['userusername'],$user['usergroup'],$user['displaygroup'])."<";
			}
		}
		if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);
	}
}

function avatarep_threads()
{
	global $db, $avatarep, $mybb, $thread, $lang, $avatar_thread, $avatarep_thread;
	
    $lang->load("avatarep", false, true);        
	 
    //Revisar si la opcion esta activa
    if($mybb->settings['avatarep_active'] == 0 && $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_temas'] == 0)
    {
        return false;
    }
	
	if(THIS_SCRIPT == "showthread.php")
	{
		if(!isset($avatarep) || !is_array($avatarep))
		{
			$uid = (int)$thread['uid'];
			$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, avatartype', "uid = '{$uid}'");
			$user = $db->fetch_array($query);						
			$avatarep = avatarep_format_avatar($user);
		}
		if($mybb->settings['avatarep_contributor'] == 1)
		{
			$tid = (int)$thread['tid'];
			$tid = $db->escape_string($tid);
			$myuid = (int)$mybb->user['uid'];
			$myuid = $db->escape_string($myuid);
			if(empty($mybb->user['avatar']))
				$mybb->user['avatar'] = "images/default_avatar.png";
			else
				$mybb->user['avatar'] = htmlspecialchars_uni($mybb->user['avatar']);
			if($mybb->settings['avatarep_onerror'] == 1)
				$onerror = " onerror=\"this.src=\'images/default_avatar.png\'\"";
			else
				$onerror = "";			
			$query = $db->simple_select("posts","uid","tid={$tid} AND uid= {$myuid}",array("limit"=>1));
			$is_avatar = $db->num_rows($query);
			if($is_avatar >= 1)
			{
				$avatarep_thread = '<img src="' . $mybb->user['avatar'] . '" alt="' . $mybb->user['username'] . '" class="avatarep_img_contributor"'.$onerror.' />';			
			}
			else
			{
				$search = "/uploads";
				$replace = "./uploads";
				$avatarep['avatar'] = str_replace($replace, $search, $avatarep['avatar']);
				$avatar_thread = $avatarep['avatar'];
				if(empty($avatar_thread))
					$avatar_thread = "images/default_avatar.png";
				else
					$avatar_thread = htmlspecialchars_uni($avatar_thread);
				$post['avatarep_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_contributor, $avatarep['username']);
				$avatarep_thread = "<img src=\"".$avatarep['avatar']."\" alt=\"".$post['avatarep_title']."\" class=\"avatarep_img_contributor\"{$onerror} />";				
			}			
		}
		else
		{
			if($mybb->settings['avatarep_onerror'] == 1)
				$onerror = " onerror=\"this.src=\'images/default_avatar.png\'\"";
			else
				$onerror = "";			
			$search = "/uploads";
			$replace = "./uploads";
			$avatarep['avatar'] = str_replace($replace, $search, $avatarep['avatar']);
			$avatar_thread = $avatarep['avatar'];
			$post['avatarep_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_contributor, $avatarep['username']);
			$avatarep_thread = "<img src=\"".htmlspecialchars_uni($avatarep['avatar'])."\" class=\"avatarep_img_contributor\" alt=\"".$post['avatarep_title']."\"{$onerror} />";			
		}	
	}	
}

function avatarep_style_guser(){
   global $mybb, $cache, $db;

   	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_format'] == 0)
    {
        return false;
    }
	
    if (empty($cache->cache['moderators']))
    {
        $cache->cache['moderators'] = $cache->read("moderators");
    }

	if(isset($cache->cache['moderators']))
	{
		foreach ($cache->cache['moderators'] as $fid => $fdata)
		{
			if (isset($fdata['usergroups']))
			{
				foreach ($fdata['usergroups'] as $gid => $gdata)
				{
					$cache->cache['moderators'][$fid]['usergroups'][$gid]['title'] = "#{$gdata['title']}{$gid}#";
					$cache->cache['usergroups'][$gid]['title'] = $gdata['title'];
					$cache->cache['groups'][] = $gid;
				}
			}
			if (isset($fdata['users']))
			{
				foreach ($fdata['users'] as $uid => $udata)
				{
					$cache->cache['moderators'][$fid]['users'][$uid]['username'] = "#{$udata['username']}{$uid}#";				
					$cache->cache['users'][$uid] = $udata['username'];
					$cache->cache['mods'][] = $uid;
				}
			}
		}		
	}
}

function avatarep_style_output(&$content){
	global $mybb, $db, $cache;

	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_format'] == 0)
    {
        return false;
    }
	if(isset($cache->cache['users']))
	{
		$cache->cache['users'] = array_unique($cache->cache['users']);
	}
	if(isset($cache->cache['guests']))
	{
		$cache->cache['guests'] = array_unique($cache->cache['guests']);
	}
	if(isset($cache->cache['mods']))
	{
		$cache->cache['mods'] = array_unique($cache->cache['mods']);
	}
	if(isset($cache->cache['groups']))
	{
		$cache->cache['groups'] = array_unique($cache->cache['groups']);
	}
	
    if (sizeof($cache->cache['users']))
    {	
        $result = $db->simple_select('users', 'uid, username, usergroup, displaygroup', 'uid IN (' . implode(',', array_keys($cache->cache['users'])) . ')');
        while ($avatarep = $db->fetch_array($result))
        {
			$username = format_name($avatarep['username'], $avatarep['usergroup'], $avatarep['displaygroup']);
			$format = "#{$avatarep['username']}{$avatarep['uid']}#";
			if(is_array($cache->cache['groups']) && !empty($cache->cache['mods']))
			{
				$compare = explode(",", $cache->cache['mods']);
				if(in_array($avatarep['uid'], $compare))
                {
                    $old_username = str_replace('{username}', $format, $cache->cache['usergroups'][$avatarep['usergroup']]['namestyle']);
                    if ($old_username != '')
                    {
                        $content = str_replace($old_username, $format, $content);
                    }
                }
				
			}
	
            $content = str_replace($format, $username, $content);			
			unset($cache->cache['users'][$avatarep['uid']]);
		}

		if (isset($fdata['users']))
		{
			foreach ($fdata['users'] as $uid => $udata)
			{
				$cache->cache['moderators'][$fid]['users'][$uid]['username'] = "#{$udata['username']}{$uid}#";				
				$cache->cache['users'][$uid] = $udata['username'];
				$cache->cache['mods'][] = $uid;
			}
		}
	}
	
	if (sizeof($cache->cache['guests']))
    {
        foreach ($cache->cache['guests'] as $username)
        {
            $format = "#{$username}#";
            $username = format_name($username, 1, 1);
            $content = str_replace($format, $username, $content);
        }
    }
        
    if (sizeof($cache->cache['groups']))
    {
        foreach ($cache->cache['usergroups'] as $gid => $gdata)
        {
            if (!in_array($gid, $cache->cache['groups']))
            {
                continue;
            }
            $title = format_name($gdata['title'], $gid);
            $format = "#{$gdata['title']}{$gid}#";
            $content = str_replace($format, $title, $content);
        }
    }	
}

function avatarep_hover_extends($id, $name)
{
	global $mybb, $lang;
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_menu'] == 0 && $mybb->settings['avatarep_menu_events'] == 2)
    {
		return false;	
	}
	$lang->load("avatarep", false, true);
	$timeloader = 500;
	$avatar_script = '<script type="text/javascript">var lpaname="'.$name.'";var lpatimer="'.$timeloader.'";</script>';
	$avatar_hover = "{$avatar_script}
<!-- Last post avatar v2.9.x extends-->";
	return $avatar_hover;
}

function avatarep_popup()
{
    global $lang, $mybb, $templates, $avatarep_popup, $db, $avatarep_script;

	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_menu'] == 0)
    {
        return false;
    }
	if($mybb->settings['avatarep_menu_events'] == 2)
	{
		$avatarep_script = "<script type=\"text/javascript\" src=\"{$mybb->asset_url}/jscripts/avatarep.js?ver=292\"></script>";
	}
    if($mybb->input['action'] == "avatarep_popup"){

	$lang->load("member", false, true);
	$lang->load("avatarep", false, true);
	$uid = intval($mybb->input['uid']);

    if($mybb->usergroup['canviewprofiles'] == 0)
    {
		if($mybb->settings['avatarep_menu_events'] == 2)
		{
			eval("\$avatarep_popup = \"".$templates->get("avatarep_popup_error_hover", 1, 0)."\";");
			echo json_encode($avatarep_popup);
		}
		else
		{		
			eval("\$avatarep_popup = \"".$templates->get("avatarep_popup_error", 1, 0)."\";");
			echo $avatarep_popup;
		}
    }
	else{
		// User is currently online and this user has permissions to view the user on the WOL
		$timesearch = TIME_NOW - $mybb->settings['wolcutoffmins']*60;
		$query = $db->simple_select("sessions", "location,nopermission", "uid='{$uid}' AND time>'{$timesearch}'", array('order_by' => 'time', 'order_dir' => 'DESC', 'limit' => 1));
		$session = $db->fetch_array($query);
		
		if(($memprofile['invisible'] != 1 || $mybb->usergroup['canviewwolinvis'] == 1 || $memprofile['uid'] == $mybb->user['uid']) && !empty($session))
		{
			$status_start = "<div class=\"avatarep_online\">";
			$status_end = "</div>";
			eval("\$online_status = \"".$templates->get("member_profile_online")."\";");
		}
		// User is offline
		else
		{
			$status_start = "<div class=\"avatarep_offline\">";
			$status_end = "</div>";		
			eval("\$online_status = \"".$templates->get("member_profile_offline")."\";");
		}

		$memprofile = get_user($uid);
		$lang->avatarep_user_alt = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($memprofile['username']));
		$lang->avatarep_user_no_avatar = htmlspecialchars_uni($lang->avatarep_user_no_avatar);
		if($memprofile['uid'] > 0 && $memprofile['avatar'] == "" || empty($memprofile['avatar'])) 
		{
			$avatarep = '<img src="images/default_avatar.png" alt="'.$lang->avatarep_user_no_avatar.'" />';
		}
		else if($memprofile['uid'] == 0 && empty($memprofile['avatar']) && $mybb->settings['avatarep_guests'] == 1) 
		{
			$avatarep = '<img src="images/default_avatar.png" alt="'.$lang->avatarep_user_no_avatar.'" />';
		}		
		else
		{
			if($mybb->settings['avatarep_onerror'] == 1)
				$onerror = " onerror=\"this.src=\'images/default_avatar.png\'\"";
			else
				$onerror = "";			
			$avatarep =  htmlspecialchars_uni($memprofile['avatar']);
			if($memprofile['avatartype'] == "gravatar")
			{
				$search = "&";
				$replace = "&amp;";
				$avatarep = str_replace($search, $replace, $avatarep);		
			}
			$memprofile['avatartype'] = htmlspecialchars_uni($memprofile['avatartype']);
			$avatarep = "<img src=\"" . $avatarep . "\" alt=\"".$lang->avatarep_user_alt."\" type=\"".$memprofile['avatartype']."\"{$onerror} />";
		}
		$memprofile['avatar'] = $status_start . $avatarep . $status_end;
		if($mybb->settings['avatarep_format'] == 1)
		{
			$formattedname = format_name($memprofile['username'], $memprofile['usergroup'], $memprofile['displaygroup']);			
		}
		else
		{
			$formattedname = htmlspecialchars_uni($memprofile['username']);			
		}
		$usertitle = "";
		if(!empty($memprofile['usertitle'])) { $usertitle = $memprofile['usertitle']; $usertitle = "($usertitle)";}
		$memregdate = my_date($mybb->settings['dateformat'], $memprofile['regdate']);
		$memprofile['postnum'] = my_number_format($memprofile['postnum']);
		$warning_link = "warnings.php?uid={$memprofile['uid']}";
		$warning_level = round($memprofile['warningpoints']/$mybb->settings['maxwarningpoints']*100);
		$memlastvisitdate = my_date($mybb->settings['dateformat'], $memprofile['lastactive']);
		$memlastvisittime = my_date($mybb->settings['timeformat'], $memprofile['lastactive']);
		if($mybb->settings['avatarep_menu_events'] == 2)
		{
			eval("\$avatarep_popup = \"".$templates->get("avatarep_popup_hover", 1, 0)."\";");
			echo json_encode($avatarep_popup);
		}
		else
		{
			eval("\$avatarep_popup = \"".$templates->get("avatarep_popup", 1, 0)."\";");
			echo $avatarep_popup;
		}	
		}
		exit;
	}
}
?>
