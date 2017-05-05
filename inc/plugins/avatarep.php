<?php
/**
*@ Autor: Dark Neo
*@ Fecha: 2013-12-12
*@ Version: 2.9.3
*@ Contacto: neogeoman@gmail.com
*/

// Inhabilitar acceso directo a este archivo
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Añadir hooks
if(THIS_SCRIPT == 'index.php' || THIS_SCRIPT == 'forumdisplay.php'){
$plugins->add_hook('build_forumbits_forum', 'forumlist_avatar');
$plugins->add_hook('forumdisplay_thread', 'avatarep_thread');
$plugins->add_hook('forumdisplay_announcement', 'avatarep_announcement');
if($settings['sidebox5'] == 0 || $settings['sidebox5'] == 1)
$plugins->add_hook('index_end', 'avatarep_portal_sb');	
}
else if(THIS_SCRIPT == 'showthread.php'){
$plugins->add_hook('showthread_end', 'avatarep_threads');
}
else if(THIS_SCRIPT == 'search.php')
{
$plugins->add_hook('search_results_thread', 'avatarep_search');
$plugins->add_hook('search_results_post', 'avatarep_search');
}
else if(THIS_SCRIPT == 'private.php'){
$plugins->add_hook('private_end', 'avatarep_private_end');
$plugins->add_hook("private_results_end", "avatarep_private_end");
$plugins->add_hook("private_tracking_end", "avatarep_private_end");
}
else if(THIS_SCRIPT == 'portal.php'){
if($settings['sidebox5'] == 1)
$plugins->add_hook("portal_end", "avatarep_portal_sb");		
else if($settings['sidebox5'] == 0)
$plugins->add_hook("portal_end", "avatarep_portal_lt");
else	
$plugins->add_hook("portal_end", "avatarep_portal_lt");	
$plugins->add_hook("portal_announcement", "avatarep_portal");	
}
$plugins->add_hook('global_start', 'avatarep_popup');
$plugins->add_hook('usercp_do_avatar_end', 'avatarep_avatar_update');
$plugins->add_hook('global_end', 'avatarep_style_guser');
$plugins->add_hook('pre_output_page', 'avatarep_style_output');
$plugins->add_hook("class_moderation_delete_post", "avatarep_deletepost");
$plugins->add_hook("class_moderation_delete_thread", "avatarep_deletepost");
$plugins->add_hook("class_moderation_soft_delete_posts", "avatarep_deletepost");
$plugins->add_hook("class_moderation_restore_posts", "avatarep_deletepost");
if(THIS_SCRIPT == 'modcp.php' && in_array($mybb->input['action'], array('do_new_announcement', 'do_edit_announcement'))){
$plugins->add_hook('redirect', 'avatarep_announcement_update');
}

// Informacion del plugin
function avatarep_info()
{
	global $db, $mybb, $lang, $avatarep_config_link;

    $lang->load("avatarep", false, true);
	$avatarep_config_link = '';

	if($mybb->settings['avatarep_active'] == 1)
	{
		$avatarep_config_link = '<div style="float: right;"><a href="index.php?module=config&amp;action=change&amp;search=avatarep" style="color:#035488; background: url(../images/icons/brick.png) no-repeat 0px 18px; padding: 18px; text-decoration: none;"> '.$db->escape_string($lang->avatarep_config).'</a></div>';
	}
	else if($mybb->settings['avatarep_active'] == 0)
	{
		$avatarep_config_link = '<div style="float: right; color: rgba(136, 17, 3, 1); background: url(../images/icons/exclamation.png) no-repeat 0px 18px; padding: 21px; text-decoration: none;">Plugin disabled</div>';
	}
	
	return array(
        "name"			=> $db->escape_string($lang->avatarep_name),
    	"description"	=> $db->escape_string($lang->avatarep_descrip) . " developers " . $avatarep_config_link,
		"website"		=> "http://www.mybb.com",
		"author"		=> "Dark Neo",
		"authorsite"	=> "http://soportemybb.es",
		"version"		=> "2.9.3",
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
        'name' => 'avatarep_temas2',
        'title' =>  $db->escape_string($lang->avatarep_thread_lastposter),
        'description' => $db->escape_string($lang->avatarep_thread_lastposter_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 40,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_temas2_mark',
        'title' =>  $db->escape_string($lang->avatarep_thread_lastposter_mark),
        'description' => $db->escape_string($lang->avatarep_thread_lastposter_mark_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 50,
        'gid' => $group['gid']
    );
	
    $avatarep_config[] = array(
        'name' => 'avatarep_anuncios',
        'title' =>  $db->escape_string($lang->avatarep_thread_announcements),
        'description' => $db->escape_string($lang->avatarep_thread_announcements_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 60,
        'gid' => $group['gid']
    );

    $avatarep_config[] = array(
        'name' => 'avatarep_contributor',
        'title' =>  $db->escape_string($lang->avatarep_thread_contributor),
        'description' => $db->escape_string($lang->avatarep_thread_contributor_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 70,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_latest_threads',
        'title' =>  $db->escape_string($lang->avatarep_latest_threads),
        'description' => $db->escape_string($lang->avatarep_latest_threads_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 80,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_private',
        'title' =>  $db->escape_string($lang->avatarep_private),
        'description' => $db->escape_string($lang->avatarep_private_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 90,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_portal',
        'title' =>  $db->escape_string($lang->avatarep_portal),
        'description' => $db->escape_string($lang->avatarep_portal_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 100,
        'gid' => $group['gid']
    );	
	
    $avatarep_config[] = array(
        'name' => 'avatarep_busqueda',
        'title' =>  $db->escape_string($lang->avatarep_search),
        'description' => $db->escape_string($lang->avatarep_search_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 110,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_menu',
        'title' =>  $db->escape_string($lang->avatarep_menu),
        'description' => $db->escape_string($lang->avatarep_menu_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 120,
        'gid' => $group['gid']
    );

	$avatarep_config[] = array(
        'name' => 'avatarep_menu_events',
        'title' =>  $db->escape_string($lang->avatarep_menu_events),
        'description' => $db->escape_string($lang->avatarep_menu_events_descrip),
        'optionscode' => 'select \n1=On Click \n2=Mouse Over',
        'value' => '1',
        'disporder' => 130,
        'gid' => $group['gid']
    );

	$avatarep_config[] = array(
        'name' => 'avatarep_guests',
        'title' =>  $db->escape_string($lang->avatarep_guests),
        'description' => $db->escape_string($lang->avatarep_guests_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 140,
        'gid' => $group['gid']
    );	
	
	$avatarep_config[] = array(
        'name' => 'avatarep_format',
        'title' =>  $db->escape_string($lang->avatarep_format),
        'description' => $db->escape_string($lang->avatarep_format_descrip),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 150,
        'gid' => $group['gid']
    );
	
	$avatarep_config[] = array(
        'name' => 'avatarep_version',
        'title' =>  "Version",
        'description' => "Plugin version of last poster avatar on threadlist and forumlist",
        'optionscode' => 'text',
        'value' => 293,
        'disporder' => 160,
        'gid' => 0
    );
    
    foreach($avatarep_config as $array => $content)
    {
        $db->insert_query("settings", $content);
    }

	// Creamos la cache de datos para nuestros avatares
	$query = $db->simple_select('announcements', 'uid');
	$query = $db->query("
		SELECT DISTINCT(a.uid) as uid, u.username, u.username AS userusername, u.avatar, u.usergroup, u.displaygroup
		FROM ".TABLE_PREFIX."announcements a
		LEFT JOIN ".TABLE_PREFIX."users u ON u.uid = a.uid	
	");

	if($db->num_rows($query))
	{
		$inline_avatars = array();
		while($user = $db->fetch_array($query))
		{
			$inline_avatars[$user['uid']] = avatarep_format_avatar($user);
		}

		$cache->update('anno_cache', $inline_avatars);
	}
	
	//Adding new templates
	$templatearray = array(
		'title' => 'avatarep_popup_hover',
		'template' => $db->escape_string('<div class="modal_avatar_hover">
<table>
	<tr>
		<td class="tavatar">
			<a href="member.php?action=profile&amp;uid={$uid}">
				<span class="trow_uname">{$formattedname}</span>
			</a>
			<br />
			<span class="trow_memprofile">
				{$usertitle}<br />
			</span>
			<span>
				{$memprofile[\'avatar\']}
			</span>
		</td>
		<td align="left" valign="middle">
			<div>
				<span class="trow_status">
					{$lang->postbit_status} {$online_status}<br />					
					{$lang->registration_date} {$memregdate}<br />
					{$lang->reputation} {$memprofile[\'reputation\']}<br />
					{$lang->total_threads} {$memprofile[\'threadnum\']}<br />					
					{$lang->total_posts} {$memprofile[\'postnum\']}<br />
					{$lang->lastvisit} {$memlastvisitdate} {$memlastvisittime}<br />	
				</span>
			</div>
		</td>
	</tr>
</table>
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
	<div class="trow"><br />{$lang->avatarep_user_error_text}<br />&nbsp;</div>
</div>'),
		'sid' => '-1',
		'version' => '1803',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);

		$templatearray = array(
		'title' => 'avatarep_popup',
		'template' => $db->escape_string('<div class="modal">
<table>
	<tr>
		<td class="tavatar">
			<a href="member.php?action=profile&amp;uid={$uid}">
				<span class="trow_uname">{$formattedname}</span>
			</a>
			<br />
			<span class="trow_memprofile">
				{$usertitle}<br />
			</span>
			<span>
				{$memprofile[\'avatar\']}
			</span>
		</td>
		<td class="trow_profile">
			<div class="trow_uprofile">
				<span class="trow_memprofile">
					<a href="member.php?action=profile&amp;uid={$uid}">{$lang->avatarep_user_profile}</a>&nbsp;&nbsp;&nbsp;
					<a href="private.php?action=send&amp;uid={$memprofile[\'uid\']}">{$lang->avatarep_user_sendpm}</a>
				</span>
				<hr class="hr" />
				<span class="trow_status">
					{$lang->postbit_status} {$online_status}<br />					
					{$lang->registration_date} {$memregdate}<br />
					{$lang->reputation} {$memprofile[\'reputation\']}<br />
					{$lang->total_threads} {$memprofile[\'threadnum\']}<br />					
					{$lang->total_posts} {$memprofile[\'postnum\']}<br />
					{$lang->lastvisit} {$memlastvisitdate} {$memlastvisittime}<br />	
					{$lang->warning_level} <a href="{$warning_link}">{$warning_level} %</a><br /><hr>
					(<a href="search.php?action=finduserthreads&amp;uid={$uid}">{$lang->find_threads}</a> &mdash; <a href="search.php?action=finduser&amp;uid={$uid}">{$lang->find_posts}</a>)
				</span>
		</div>
		</td>
	</tr>
</table>
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
	<div class="trow"><br />{$lang->avatarep_user_error_text}<br />&nbsp;</div>
</div>'),
		'sid' => '-1',
		'version' => '1803',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);

	$cache->update_forums();
	rebuild_settings();
}

function avatarep_deactivate() {
    //Variables que vamos a utilizar
	global $mybb, $cache, $db;
    // Borrar el grupo de opciones
	$db->delete_query("settings", "name IN ('avatarep_active','avatarep_foros','avatarep_temas','avatarep_temas2','avatarep_anuncios','avatarep_portal','avatarep_busqueda','avatarep_menu','avatarep_menu_events','avatarep_guests','avatarep_format','avatarep_version')");
	$db->delete_query("settinggroups", "name='avatarep'");
	$db->delete_query('datacache', "title = 'anno_cache'");
	$db->delete_query("templates", "title IN('avatarep_popup', 'avatarep_popup_error', 'avatarep_popup_hover', 'avatarep_popup_error_hover')");
	
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

		return array(
			'avatar' => $avatar,
			'avatarep' => '<img src="' . $avatar . '" class="avatarep_img" alt="'.htmlspecialchars_uni($user['userusername']).'" />',
			'username' => htmlspecialchars_uni($user['userusername']),
			'profilelink' => get_profile_link($user['uid']),
			'uid' => (int)$user['uid'],
			'usergroup' => (int)$user['usergroup'],
			'displaygroup' => (int)$user['displaygroup']
		);

	return format_avatar($user);
}		

function avatarep_deletepost()
{
	global $cache, $mybb;
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_foros'] == 0)
    {
		return false;	
	}	
	if(isset($cache->cache['avatarep_cache']))
	{
		$avatarep_cache = $cache->update('avatarep_cache');	
	}
}

// Avatar en foros
function forumlist_avatar(&$_f)
{
	global $cache, $db, $fcache, $mybb, $lang, $avatar_events;

    // Cargamos idioma
    $lang->load("avatarep", false, true);
    
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_foros'] == 0)
    {
		return false;	
	}
	
	if(!isset($cache->cache['avatarep_cache']))
	{
		$cache->cache['avatarep_cache'] = array();
		$avatarep_cache = $cache->read('avatarep_cache');

		$forums = new RecursiveIteratorIterator(new RecursiveArrayIterator($fcache));

		// Sentencia que busca el creador de los temas, cuando existen subforos...
		foreach($forums as $_forum)
		{
			$forum = $forums->getSubIterator();

			if($forum['fid'])
			{
				$forum = iterator_to_array($forum);
				$avatarep_cache[$forum['fid']] = $forum;
				if($private_forums[$forum['fid']]['lastpost'])
				{
					$forum['lastpost'] = $private_forums[$forum['fid']]['lastpost'];
					$lastpost_data = array(
						"lastpost" => $private_forums[$forum['fid']]['lastpost'],
						"lastpostsubject" => $private_forums[$forum['fid']]['subject'],
						"lastposter" => $private_forums[$forum['fid']]['lastposter'],
						"lastposttid" => $private_forums[$forum['fid']]['tid'],
						"lastposteruid" => $private_forums[$forum['fid']]['lastposteruid']
					);
				}
				else
				{
					$lastpost_data = array(
						"lastpost" => $forum['lastpost'],
						"lastpostsubject" => $forum['lastpostsubject'],
						"lastposter" => $forum['lastposter'],
						"lastposttid" => $forum['lastposttid'],
						"lastposteruid" => $forum['lastposteruid']
					);
				}			
				// Fetch subforums of this forum
				if(isset($fcache[$forum['fid']]))
				{
					$forum_info = build_forumbits($forum['fid'], $depth+1);
					// If the child forums' lastpost is greater than the one for this forum, set it as the child forums greatest.
					if($forum_info['lastpost']['lastpost'] > $lastpost_data['lastpost'])
					{
						$lastpost_data = $forum_info['lastpost'];
					}

					$sub_forums = $forum_info['forum_list'];
				}
				// If the current forums lastpost is greater than other child forums of the current parent, overwrite it
				if(!isset($parent_lastpost) || $lastpost_data['lastpost'] > $parent_lastpost['lastpost'])
				{
					$parent_lastpost = $lastpost_data;
				}			
				if(isset($avatarep_cache) && $lastpost_data['lastposteruid'] > 0){	
					$avatarep_cache[$forum['fid']]['avataruid'] = $lastpost_data['lastposteruid'];							
					$avatarep_cache[$forum['fid']]['lastpost'] = $lastpost_data['lastpost'];
					$avatarep_cache[$forum['fid']]['lastposter'] = $lastpost_data['lastposter'];	
				}
			}
		}
			
		// Esta sentencia ordena los usuarios por usuario/foro
		$users = array();
		foreach($avatarep_cache as $forum)
		{
			if(isset($forum['avataruid']))
			{
				$users[$forum['avataruid']][] = $forum['fid'];
			}
		}

		// Esta sentecia trae la información de los avatares de usuario
		if(!empty($users))
		{
			$sql = implode(',', array_keys($users));
			$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, avatartype, usergroup, displaygroup', "uid IN ({$sql})");

			while($user = $db->fetch_array($query))
			{
				// Finalmente, se le asigna el avatar a cada uno de ellos, los traidos en la sentencia.
				$avatar = avatarep_format_avatar($user); 				
				foreach($users[$user['uid']] as $aid)
				{
					$avatarep_cache[$aid]['avatarep_avatar'] = $avatar;
				}	
			}
		}

		// Aplicamos los cambios! Reemplazando las lineas de código para guardarlas en cache...
		$cache->cache['avatarep_cache'] = $avatarep_cache;	
	}
	
	$_f['avatarep_lastpost'] = $cache->cache['avatarep_cache'][$_f['fid']]['avatarep_avatar'];	
	$_f['uid'] = (int)$_f['avatarep_lastpost']['uid'];
	if($mybb->settings['avatarep_menu_events'] == 2)
	{
		$avatar_events = "onmouseover";
	}
	else
	{
		$avatar_events = "onclick";		
	}	
	$myid = (int)$_f['fid'];
	$_f['avatarep_title'] = $lang->sprintf($lang->avatarep_user_alt_forums, htmlspecialchars_uni($_f['avatarep_lastpost']['username']));
	if($mybb->settings['avatarep_menu'] == 1){
		if(function_exists("google_seo_url_profile")){
			if($mybb->settings['avatarep_menu_events'] == 2)
			{		
				$_f['avatarep'] = "<a href=\"" . $_f['avatarep_lastpost']['profilelink'] . "?action=avatarep_popup\" title=\"".$_f['avatarep_title']."\" id=\"forum_member{$myid}\" class=\"forum_member{$f_['uid']}\" onclick=\"return false;\">".$_f['avatarep_lastpost']['avatarep']."</a>".avatarep_hover_extends($myid,"forum_member");
			}
			else
			{
				$_f['avatarep'] = "<a href=\"javascript:void(0)\" id=\"forum_member{$_f['fid']}\" title=\"".$_f['avatarep_title']."\" {$avatar_events}=\"MyBB.popupWindow('". $_f['avatarep_lastpost']['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$_f['avatarep_lastpost']['avatarep']."</a>";		
			}
		}
		else
		{
			if($mybb->settings['avatarep_menu_events'] == 2)
			{		
				$_f['avatarep'] = "<a href=\"member.php?uid={$_f['uid']}&amp;action=avatarep_popup\" id=\"forum_member{$myid}\" class=\"forum_member{$f_['uid']}\" title=\"".$_f['avatarep_title']."\" onclick=\"return false;\">".$_f['avatarep_lastpost']['avatarep']."</a>".avatarep_hover_extends($myid,"forum_member");
			}
			else
			{	
				$_f['avatarep'] = "<a href=\"javascript:void(0)\" id=\"forum_member{$_f['fid']}\" title=\"".$_f['avatarep_title']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$_f['uid']}&amp;action=avatarep_popup', null, true); return false;\">".$_f['avatarep_lastpost']['avatarep']."</a>";
			}
		}
	}else{
		$_f['avatarep'] = "<a href=\"". $_f['avatarep_lastpost']['profilelink'] . "\" id=\"forum_member{$_f['fid']}\" title=\"".$_f['avatarep_title']."\">".$_f['avatarep_lastpost']['avatarep']."</a>";
	}	
	if($mybb->settings['avatarep_guests'] == 1 && $_f['avatarep_lastpost']['uid'] === NULL)
	{
		$_f['avatarep_alt'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($_f['lastposter']));	
		$_f['avatarep'] = '<img class="avatarep_img" src="images/default_avatar.png" alt="'.$_f['avatarep_alt'].'" />';
	}
	if($mybb->settings['avatarep_format'] == 1)
	{
		if($mybb->version_code >= 1808)
		{
			if($_f['avatarep_lastpost']['username'])
			{
				$cache->cache['users'][$_f['lastposteruid']] = $_f['lastposter'];
				$_f['lastposter'] = "#{$_f['avatarep_lastpost']['username']}{$_f['avatarep_lastpost']['uid']}#";								
			}
			else
				$_f['lastposter'] = htmlspecialchars_uni($_f['lastposter']);				
		}
		else
		{
			$username = format_name($_f['avatarep_lastpost']['username'], $_f['avatarep_lastpost']['usergroup'], $_f['avatarep_lastpost']['displaygroup']);	
			$_f['lastposterav'] = $username;
			$_f['lastposter'] = build_profile_link($_f['lastposterav'], $_f['avatarep_lastpost']['uid']);				
		}
	}
	$_f['avatarep'] = '<div class="avatarep_fd">' . $_f['avatarep'] . '</div>';
}

// Avatar en temas
function avatarep_thread() {

	// Puedes definir las variables deseadas para usar en las plantillas
	global $cache, $avbr, $db, $lang, $avatarep_avatar, $avatarep_firstpost, $avatarep_lastpost, $mybb, $post, $search, $thread, $threadcache, $thread_cache, $avatar_events, $tcache;
	static $avatarep_cache, $avatarep_type;

    $lang->load("avatarep", false, true);        
	$avbr = "<br />";

    //Revisar si la opcion esta activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_temas'] == 0 && $mybb->settings['avatarep_temas2'] == 0)
    {
        return false;
    }

	if($mybb->settings['avatarep_temas'] == 1 && $mybb->settings['avatarep_guests'] == 1 && $thread['uid'] == 0 && $thread['username'] != "")
	{
		$thread['thread_alt'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['threadusername']));		
		$avatarep_avatar['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$thread['thread_alt']}' />";
	}
	else if($mybb->settings['avatarep_temas'] == 1 && $mybb->settings['avatarep_guests'] == 0 && $thread['uid'] == 0 && $thread['username'] != "")
	{
		$avatarep_avatar['avatarep'] = "";
	}
	if($mybb->settings['avatarep_temas2'] == 1 && $mybb->settings['avatarep_guests'] == 1 && $thread['lastposteruid'] == 0 && $thread['lastposter'] != "")
	{
		$thread['thread_alt_lp'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['lastposter']));		
		$avatarep_lastpost['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$thread['thread_alt_lp']}' />";
	}
	else if($mybb->settings['avatarep_temas2'] == 1 && $mybb->settings['avatarep_guests'] == 0 && $thread['lastposteruid'] == 0 && $thread['lastposter'] != "")
	{
		$avatarep_lastpost['avatarep'] = "";
	}
	if(!isset($avatarep_cache))
	{
		$users = $avatarep_cache = array();
		$tcache = ($thread_cache) ? $thread_cache : $threadcache;

		if(isset($tcache))
		{
			// Obtenemos los resultados en lista de temas y la busqueda
			foreach($tcache as $t)
			{
				if(!in_array($t['uid'], $users))
				{
					$users[] = "'".intval($t['uid'])."'"; // El autor del tema
				}
				if(!in_array($t['lastposteruid'], $users))
				{
					$users[] = "'".intval($t['lastposteruid'])."'"; // El ultimo envio (Si no es el autor del tema)
				}		
			}

			if(!empty($users))
			{
				$sql = implode(',', $users);
				$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup, displaygroup, avatartype', "uid IN ({$sql})");
					
				while($user = $db->fetch_array($query))
				{
					$avatarep_cache[$user['uid']] = avatarep_format_avatar($user);					
				}

			}
		}
	}

	if(empty($avatarep_cache))
	{
		return; // Si no hay avatares...
	}

	$uid = ($post['uid']) ? $post['uid'] : $thread['uid']; // Siempre debe haber un autor

	if(isset($avatarep_cache[$uid]))
	{
		$avatarep_avatar = $avatarep_cache[$uid];
	}

	if(isset($avatarep_cache[$thread['lastposteruid']]))
	{
		$avatarep_lastpost = $avatarep_cache[$thread['lastposteruid']]; // Unicamente para los últimos envios
	}

	if($mybb->settings['avatarep_menu_events'] == 2)
	{
		$avatar_events = "onmouseover";
	}
	else
	{
		$avatar_events = "onclick";		
	}	
	$myid = (int)$thread['tid'];
   if($mybb->settings['avatarep_temas'] == 1 && $thread['uid'] > 0)
   {
		if($mybb->settings['avatarep_format'] == 1)
		{
			if($mybb->version_code >= 1808)
			{
				if($thread['username'])
				{
					$cache->cache['users'][$thread['uid']] = $thread['username'];
					$thread['username'] = "#{$avatarep_avatar['username']}{$avatarep_avatar['uid']}#";					
				}
				else
					$thread['username'] = htmlspecialchars_uni($thread['username']);			
			}
			else
			{
				$thread['ownerav'] = format_name($avatarep_avatar['username'], $avatarep_avatar['usergroup'], $avatarep_avatar['displaygroup']);
				$thread['username'] = build_profile_link($thread['ownerav'], $avatarep_avatar['uid']);				
			}
		}
		else
		{
			$thread['username'] = htmlspecialchars_uni($thread['username']);			
		}
		$uid = (int)$avatarep_avatar['uid'];
		$myid = $myid;
		if($mybb->settings['avatarep_menu'] == 1)
		{
			if(function_exists("google_seo_url_profile"))
			{
				if($mybb->settings['avatarep_menu_events'] == 2)
				{		
					$avatarep_avatar['thread_first_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($avatarep_avatar['username']));
					$avatarep_avatar['avatarep'] = "<a href=\"". $avatarep_avatar['profilelink'] . "?action=avatarep_popup\" id=\"tal_member{$thread['tid']}\" class=\"tal_member{$uid}\" title=\"".$avatarep_avatar['thread_first_title']."\" onclick=\"return false;\">".$avatarep_avatar['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");
				}
				else
				{
					$avatarep_avatar['thread_first_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($avatarep_avatar['username']));					
					$avatarep_avatar['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tal_member{$thread['tid']}\" title=\"".$avatarep_avatar['thread_first_title']."\" {$avatar_events}=\"MyBB.popupWindow('". $avatarep_avatar['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$avatarep_avatar['avatarep']."</a>";  				
				}
			}
			else
			{
				if($mybb->settings['avatarep_menu_events'] == 2)
				{		
					$avatarep_avatar['thread_first_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($avatarep_avatar['username']));			
					$avatarep_avatar['avatarep'] = "<a href=\"{$avatarep_avatar['profilelink']}&amp;action=avatarep_popup\" id=\"tal_member{$thread['tid']}\" class=\"tal_member{$uid}\" title=\"{$avatarep_avatar['thread_first_title']}\" onclick=\"return false;\">".$avatarep_avatar['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");
				}
				else
				{						
					$avatarep_avatar['thread_first_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($avatarep_avatar['username']));			
					$avatarep_avatar['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tal_member{$thread['tid']}\" title=\"".$avatarep_avatar['thread_first_title']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$avatarep_avatar['avatarep']."</a>";
				}
			}		
		}
		else
		{
			$avatarep_avatar['thread_first_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($avatarep_avatar['username']));						
			$avatarep_avatar['avatarep'] = "<a href=\"". $avatarep_avatar['profilelink'] . "\" id=\"tal_member{$thread['tid']}\" title=\"".$avatarep_avatar['thread_first_title']."\">".$avatarep_avatar['avatarep']."</a>";
		}
		if($thread['uid'] == $mybb->user['uid'])
			$avatarep_avatar['avatarep'] = '<div class="avatarep_fda_mine">' . $avatarep_avatar['avatarep'] . '</div>';	 
		else
			$avatarep_avatar['avatarep'] = '<div class="avatarep_fda">' . $avatarep_avatar['avatarep'] . '</div>';	 
	}
	else
	{
		$thread['thread_alt'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['threadusername']));		
		$avatarep_avatar['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$thread['thread_alt']}' />";
	}
	
    if($mybb->settings['avatarep_temas2'] == 1 && $thread['lastposteruid'] > 0)
	{
		if($mybb->settings['avatarep_format'] == 1)
		{	
			if($mybb->version_code >= 1808)
			{
				if($thread['lastposter'])
				{
					$cache->cache['users'][$thread['lastposteruid']] = $thread['lastposter'];
					$thread['lastposter'] = "#{$avatarep_lastpost['username']}{$avatarep_lastpost['uid']}#";					
				}
				else
					$thread['lastposter'] = htmlspecialchars_uni($thread['lastposter']);
			}
			else
			{	
				$thread['lastposterav'] = format_name($avatarep_lastpost['username'], $avatarep_lastpost['usergroup'], $avatarep_lastpost['displaygroup']);
				$thread['lastposter'] = build_profile_link($thread['lastposterav'], $avatarep_lastpost['uid']);	
			}
		}	
		else
		{
			$thread['lastposter'] = htmlspecialchars_uni($thread['lastposter']);			
		}
		$uid = (int)$avatarep_lastpost['uid'];
		$myid = $myid."2";
		if($mybb->settings['avatarep_menu'] == 1)
		{
			if(function_exists("google_seo_url_profile"))
			{
				if($mybb->settings['avatarep_menu_events'] == 2)
				{		
					$avatarep_avatar['thread_last_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, htmlspecialchars_uni($avatarep_lastpost['username']));
					$avatarep_lastpost['avatarep'] = "<a href=\"". $avatarep_lastpost['profilelink'] . "?action=avatarep_popup\" id=\"tal_member{$myid}\" class=\"tal_member{$uid}\" title=\"".$avatarep_avatar['thread_last_title']."\" onclick=\"return false;\">".$avatarep_lastpost['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");
				}
				else
				{	
					$avatarep_avatar['thread_last_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, htmlspecialchars_uni($avatarep_lastpost['username']));			
					$avatarep_lastpost['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tao_member{$thread['tid']}\" title=\"".$avatarep_avatar['thread_last_title']."\" {$avatar_events}=\"MyBB.popupWindow('". $avatarep_lastpost['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$avatarep_lastpost['avatarep']."</a>";
				}
			}
			else
			{
				if($mybb->settings['avatarep_menu_events'] == 2)
				{		
					$avatarep_avatar['thread_last_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, htmlspecialchars_uni($avatarep_lastpost['username']));			
					$avatarep_lastpost['avatarep'] = "<a href=\"". $avatarep_lastpost['profilelink'] . "&amp;action=avatarep_popup\" id=\"tal_member{$myid}\" class=\"tal_member{$uid}\" title=\"".$avatarep_avatar['thread_last_title']."\" onclick=\"return false;\">".$avatarep_lastpost['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");
				}
				else
				{	
					$avatarep_avatar['thread_last_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, htmlspecialchars_uni($avatarep_lastpost['username']));			
					$avatarep_lastpost['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tao_member{$thread['tid']}\" title=\"".$avatarep_avatar['thread_last_title']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$avatarep_lastpost['avatarep']."</a>";
				}
			}			
		}
		else
		{
			$avatarep_avatar['thread_last_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, htmlspecialchars_uni($avatarep_lastpost['username']));						
			$avatarep_lastpost['avatarep'] = 	"<a href=\"". $avatarep_lastpost['profilelink'] . "\" id=\"tao_member{$thread['tid']}\" title=\"".$avatarep_avatar['thread_last_title']."\">".$avatarep_lastpost['avatarep']."</a>";
		}
		$mybb->user['avatar'] = htmlspecialchars_uni($mybb->user['avatar']);
		$mybb->user['username'] = htmlspecialchars_uni($mybb->user['username']);
		if($mybb->settings['avatarep_temas2_mark'] == 1)
		{
			if($thread['lastposteruid'] == $mybb->user['uid'] || $thread['uid'] == $mybb->user['uid'])
			{
				$thread['avatarep'] = '<div class="avatarep_fdl_mine"><img src="' . $mybb->user['avatar'] . '" alt="' . $mybb->user['username'] . '" class="avatarep_fdl_img" /></div>';			
				$avatarep_lastpost['avatarep'] = '<div class="avatarep_fdl_mine">' . $avatarep_lastpost['avatarep'] . '</div>';
			}		
			/*else if(!$mybb->user['uid'])
			{
				$thread['avatarep'] = '<div class="avatarep_fdl_mine"><img src="' . $avatarep_lastpost['avatar'] . '" alt="' . $avatarep_lastpost['username'] . '" class="avatarep_fdl_img" /></div>';			
			}*/
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
					$thread['avatarep'] = '<div class="avatarep_fdl_mine"><img src="' . $mybb->user['avatar'] . '" alt="' . $mybb->user['username'] . '" class="avatarep_fdl_img" /></div>';			
				}
				$avatarep_lastpost['avatarep'] = '<div class="avatarep_fdl">' . $avatarep_lastpost['avatarep'] . '</div>';			
			}
			else
			{
				$thread['avatarep'] = "";
				$avatarep_lastpost['avatarep'] = '<div class="avatarep_fdl">' . $avatarep_lastpost['avatarep'] . '</div>';			
			}
			
		}
		else
		{
			$thread['avatarep'] = "";
			$avatarep_lastpost['avatarep'] = '<div class="avatarep_fdl">' . $avatarep_lastpost['avatarep'] . '</div>';
		}
	}
	else
	{
		$thread['thread_alt_lp'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['lastposter']));		
		$avatarep_lastpost['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$thread['thread_alt_lp']}' />";		
	}
	
    if($mybb->settings['avatarep_temas'] == 0)
	{
		//$thread['username'] = "";
		$avatarep_avatar['avatarep'] = "";
	}	

	if($mybb->settings['avatarep_temas2'] == 0)
	{
		//$thread['lastposter']= "";
		$avatarep_lastpost['avatarep']= "";
	}
}


// Actualizar si hay un nuevo avatar
function avatarep_avatar_update()
{
    global $cache, $db, $extra_user_updates, $mybb, $updated_avatar, $user;

    $user = ($user) ? $user : $mybb->user;
    $inline_avatars = $cache->read('anno_cache');

    if(!$inline_avatars[$user['uid']])
    {
        return;
    }

    $update = ($extra_user_updates) ? $extra_user_updates : $updated_avatar;

    if(is_array($update))
    {
        $user = array_merge($user, $update);    
        $inline_avatars[$user['uid']] = avatarep_format_avatar($user);
        $cache->update('anno_cache', $inline_avatars);
    }
} 

// Avatar en anuncions
function avatarep_announcement()
{
	global $announcement, $cache, $anno_avatar, $mybb, $lang, $avatar_events;

	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_anuncios'] == 0)
    {
        return False;
    }
	
    $lang->load("avatarep", false, true); 
	$inline_avatars = $cache->read('anno_cache');
	
	if($inline_avatars[$announcement['uid']])
	{
		$anno_avatar = array(
			'avatar' => $inline_avatars[$announcement['uid']]['avatar'],
			'avatarep' => $inline_avatars[$announcement['uid']]['avatarep'],			
			'username' => $inline_avatars[$announcement['uid']]['username'], 
			'uid' => $inline_avatars[$announcement['uid']]['uid'],			
			'usergroup' => $inline_avatars[$announcement['uid']]['usergroup'],
			'displaygroup' => $inline_avatars[$announcement['uid']]['displaygroup'], 			
			'profilelink' => $inline_avatars[$announcement['uid']]['profilelink']
		);
		
	}
	if($mybb->settings['avatarep_format'] == 1)
	{
		if($mybb->version_code >= 1808)
		{
			$cache->cache['users'][$announcement['uid']] = $announcement['username'];
			$announcement['username'] = "#{$announcement['username']}{$anno_avatar['uid']}#";
			$announcement['profilelink'] = build_profile_link($announcement['username'], $anno_avatar['uid']);
		}
		else
		{		
			$announcement['username'] = format_name($announcement['username'], $anno_avatar['usergroup'], $anno_avatar['displaygroup']);
			$announcement['profilelink'] = build_profile_link($announcement['username'], $anno_avatar['uid']);				
		}
	}	
	$uid = $anno_avatar['uid'];
	if($mybb->settings['avatarep_guests'] == 1 && $uid == 0)
	{
		$anno_avatar['title'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($announcement['username']));		
		$anno_avatar['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$anno_avatar['title']}' />";
	}	
	if($mybb->settings['avatarep_menu_events'] == 2)
	{
		$avatar_events = "onmouseover";
	}
	else
	{
		$avatar_events = "onclick";		
	}	
	if($mybb->settings['avatarep_menu'] == 1)
	{
		$myid = (int)$announcement['aid'];
		$uid = (int)$announcement['uid'];		
		if(function_exists("google_seo_url_profile"))
		{
			if($mybb->settings['avatarep_menu_events'] == 2)
			{		
				$anno_avatar['title'] = $lang->sprintf($lang->avatarep_user_alt_thread_announcement, $anno_avatar['username']);		
				$anno_avatar['avatarep'] = "<a href=\"". $anno_avatar['profilelink'] . "?action=avatarep_popup\" id=\"tal_member{$myid}\" class=\"tal_member{$uid}\" title=\"".$anno_avatar['title']."\" onclick=\"return false;\">".$anno_avatar['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");
			}
			else
			{				
				$anno_avatar['title'] = $lang->sprintf($lang->avatarep_user_alt_thread_announcement, $anno_avatar['username']);				
				$anno_avatar['avatarep'] = "<a href=\"javascript:void(0)\" id=\"aa_member{$thread['tid']}\" title=\"".$anno_avatar['title']."\" {$avatar_events}=\"MyBB.popupWindow('". $anno_avatar['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$anno_avatar['avatarep']."</a>";
			}
		}
		else
		{			
			if($mybb->settings['avatarep_menu_events'] == 2)
			{		
				$anno_avatar['title'] = $lang->sprintf($lang->avatarep_user_alt_thread_announcement, $anno_avatar['username']);		
				$anno_avatar['avatarep'] = "<a href=\"". $anno_avatar['profilelink'] . "&amp;action=avatarep_popup\" id=\"tal_member{$myid}\" class=\"tal_member{$uid}\" title=\"".$anno_avatar['title']."\" onclick=\"return false;\">".$anno_avatar['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");
			}
			else
			{
				$anno_avatar['title'] = $lang->sprintf($lang->avatarep_user_alt_thread_announcement, $anno_avatar['username']);										
				$anno_avatar['avatarep'] = "<a href=\"javascript:void(0)\" id=\"aa_member{$thread['tid']}\" title=\"".$anno_avatar['title']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$anno_avatar['avatarep']."</a>";
			}
		}	
	}
	else
	{
		$anno_avatar['title'] = $lang->sprintf($lang->avatarep_user_alt_thread_announcement, $anno_avatar['username']);		
		$anno_avatar['avatarep'] = "<a href=\"". $anno_avatar['profilelink'] . "\" id=\"aa_member{$thread['tid']}\" title=\"".$anno_avatar['title']."\">".$anno_avatar['avatarep']."</a>";
	}
	$anno_avatar['avatarep'] = '<div class="avatarep_fdan">' . $anno_avatar['avatarep'] . '</div>';
}

function avatarep_announcement_update($args)
{
	global $cache, $db, $insert_announcement, $mybb, $update_announcement;

	$inline_avatars = $cache->read('anno_cache');
	$anno = ($update_announcement) ? $update_announcement : $insert_announcement;

	if(is_array($inline_avatars) && $inline_avatars[$anno['uid']])
	{
		return; //  No hay necesidad de recrear la cache...
	}

	if($anno['uid'] == $mybb->user['uid'])
	{
		$inline_avatars[$anno['uid']] = avatarep_format_avatar($mybb->user);
	}
	else
	{
		$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, usergroup, displaygroup, avatartype', "uid = '{$anno['uid']}'");

		$user = $db->fetch_array($query);

		$inline_avatars[$user['uid']] = avatarep_format_avatar($user);
	}

	$cache->update('anno_cache', $inline_avatars);
}

function avatarep_threads()
{
	global $db, $avatarep, $mybb, $thread, $lang, $avatar_thread, $avatarep_thread;
	
    $lang->load("avatarep", false, true);        
	 
    //Revisar si la opcion esta activa
    if($mybb->settings['avatarep_active'] == 0)
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
			$query = $db->simple_select("posts","uid","tid={$tid} AND uid= {$myuid}",array("limit"=>1));
			$is_avatar = $db->num_rows($query);
			if($is_avatar >= 1)
			{
				$avatarep_thread = '<img src="' . $mybb->user['avatar'] . '" alt="' . $mybb->user['username'] . '" class="avatarep_img_contributor" />';			
			}
			else
			{
				$search = "/uploads";
				$replace = "./uploads";
				$avatarep['avatar'] = str_replace($replace, $search, $avatarep['avatar']);
				$avatar_thread = $avatarep['avatar'];
				$post['avatarep_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_contributor, $avatarep['username']);
				$avatarep_thread = "<img src=\"".htmlspecialchars_uni($avatarep['avatar'])."\" alt=\"".$post['avatarep_title']."\" class=\"avatarep_img_contributor\" />";				
			}			
		}
		else
		{
			$search = "/uploads";
			$replace = "./uploads";
			$avatarep['avatar'] = str_replace($replace, $search, $avatarep['avatar']);
			$avatar_thread = $avatarep['avatar'];
			$post['avatarep_title'] = $lang->sprintf($lang->avatarep_user_alt_thread_contributor, $avatarep['username']);
			$avatarep_thread = "<img src=\"".htmlspecialchars_uni($avatarep['avatar'])."\" class=\"avatarep_img_contributor\" alt=\"".$post['avatarep_title']."\" />";			
		}	
	}	
}

function avatarep_search()
{
	global $db, $lang, $avbr, $avatarep_avatar, $avatarep_firstpost, $avatarep_lastpost, $mybb, $myid, $post, $search, $thread, $threadcache, $thread_cache, $lastposterlink, $avatar_events;
	static $avatarep_cache;
	
    $lang->load("avatarep", false, true);    
    $avbr = "<br />";
    //Revisar si la opcion esta activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_busqueda'] == 0)
    {
        return false;
    }

	if($mybb->settings['avatarep_menu_events'] == 2)
	{
		$avatar_events = "onmouseover";
	}
	else
	{
		$avatar_events = "onclick";		
	}	
	
	if($mybb->settings['avatarep_temas'] == 1 && $mybb->settings['avatarep_guests'] == 1 && $post['uid'] == 0 && $post['username'] != "")
	{
		$avatarep_avatar['avatarep_alt'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($post['username']));		
		$avatarep_avatar['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$avatarep_avatar['avatarep_alt']}' />";
	}
	else if($mybb->settings['avatarep_temas'] == 1 && $mybb->settings['avatarep_guests'] == 0 && $post['uid'] == 0 && $post['username'] != "")
	{
		$avatarep_avatar['avatarep'] = "";
	}
	if($mybb->settings['avatarep_temas'] == 1 && $mybb->settings['avatarep_guests'] == 1 && $thread['uid'] == 0 && $thread['username'] != "")
	{
		$avatarep_avatar['avatarep_alt'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['threadusername']));		
		$avatarep_avatar['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$avatarep_avatar['avatarep_alt']}' />";
	}
	else if($mybb->settings['avatarep_temas'] == 1 && $mybb->settings['avatarep_guests'] == 0 && $thread['uid'] == 0 && $thread['username'] != "")
	{
		$avatarep_avatar['avatarep'] = "";
	}
	if($mybb->settings['avatarep_temas2'] == 1 && $mybb->settings['avatarep_guests'] == 1 && $thread['lastposteruid'] == 0 && $thread['lastposter'] != "")
	{
		$avatarep_avatar['avatarep_alt'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['lastposter']));		
		$avatarep_lastpost['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$avatarep_avatar['avatarep_alt']}' />";
	}
	else if($mybb->settings['avatarep_temas2'] == 1 && $mybb->settings['avatarep_guests'] == 0 && $thread['lastposteruid'] == 0 && $thread['lastposter'] != "")
	{
		$avatarep_lastpost['avatarep'] = "";
	}
	
	if(!isset($avatarep_cache))
	{
		$users = $avatarep_cache = array();
		$cache = ($thread_cache) ? $thread_cache : $threadcache;

		if(isset($cache))
		{
			// Obtenemos los resultados en lista de temas y la busqueda
			foreach($cache as $t)
			{
				if(!in_array($t['uid'], $users))
				{
					$users[] = "'".intval($t['uid'])."'"; // El autor del tema
				}
				if(!in_array($t['lastposteruid'], $users))
				{
					$users[] = "'".intval($t['lastposteruid'])."'"; // El ultimo envio (Si no es el autor del tema)
				}		
			}			
			if(!empty($users))
			{
				$sql = implode(',', $users);
				$query = $db->simple_select('users', 'uid, username, username AS userusername, avatar, avatartype, usergroup, displaygroup', "uid IN ({$sql})");
				
				while($user = $db->fetch_array($query))
				{
					$avatarep_cache[$user['uid']] = avatarep_format_avatar($user);					
				}
			}
		}
		else if(!empty($search['posts']))
		{
			$query = $db->query("
				SELECT u.uid, u.username, u.username as userusername, u.avatar, u.avatartype, u.usergroup, u.displaygroup
				FROM ".TABLE_PREFIX."posts p
				LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid = p.uid)
				WHERE p.pid IN ({$search['posts']})
			");
			while($user = $db->fetch_array($query))
			{
				if(!isset($avatarep_cache[$user['uid']]))
				{
					$avatarep_cache[$user['uid']] = avatarep_format_avatar($user);
				}
			}
		}
	}

	if(empty($avatarep_cache))
	{
		return; // Si no hay avatares...
	}

	$uid = ($post['uid']) ? $post['uid'] : $thread['uid']; // Siempre debe haber un autor

	if(isset($avatarep_cache[$uid]))
	{
		$avatarep_avatar = $avatarep_cache[$uid];
	}

	if(isset($avatarep_cache[$thread['lastposteruid']]))
	{
		$avatarep_lastpost = $avatarep_cache[$thread['lastposteruid']]; // Unicamente para los últimos envios
	}

	if($mybb->settings['avatarep_format'] == 1 && $post['uid'] > 0)
	{
		$avatarep_avatar['avatarep_title_first'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, $avatarep_avatar['username']);									
		$post['profilelink'] = "<a href=\"". $avatarep_avatar['profilelink'] . "\" title=\"".$avatarep_avatar['avatarep_title_first']."\">".format_name($avatarep_avatar['username'], $avatarep_avatar['usergroup'], $avatarep_avatar['displaygroup'])."</a>";		
	}
	if($mybb->settings['avatarep_format'] == 1 && $thread['uid'] > 0)
	{	
		$avatarep_avatar['avatarep_title_first'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, $avatarep_avatar['username']);									
		$thread['profilelink'] = "<a href=\"". $avatarep_avatar['profilelink'] . "\" title=\"".$avatarep_avatar['avatarep_title_first']."\">".format_name($avatarep_avatar['username'], $avatarep_avatar['usergroup'], $avatarep_avatar['displaygroup'])."</a>";
	}
	if($mybb->settings['avatarep_format'] == 1 && $thread['lastposteruid'] > 0)
	{
		$avatarep_avatar['avatarep_title_last'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, $avatarep_lastpost['username']);											
		$lastposterlink = "<a href=\"". $avatarep_lastpost['profilelink'] . "\" title=\"".$avatarep_avatar['avatarep_title_last']."\">".format_name($avatarep_lastpost['username'], $avatarep_lastpost['usergroup'], $avatarep_lastpost['displaygroup'])."</a>";
	}
	$uid = intval($avatarep_avatar['uid']);		
	$uid2 = intval($avatarep_lastpost['uid']);	
	$myid = (int)$thread['tid'];
	if($myid == ""){$myid = (int)$post['pid'];}
	if($mybb->settings['avatarep_menu'] == '1')
	{
		$lang->avatarep_user_no_avatar = htmlspecialchars_uni($lang->avatarep_user_no_avatar);		
		if(function_exists("google_seo_url_profile"))
		{
			if($mybb->settings['avatarep_menu_events'] == 2)
			{
				$avatarep_avatar['avatarep_title_first'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, $avatarep_avatar['username']);															
				$avatarep_avatar['avatarep'] = "<a href=\"". $avatarep_avatar['profilelink'] . "?action=avatarep_popup\" id=\"tal_member{$myid}\" class=\"tal_member{$uid}\" title=\"".$avatarep_avatar['avatarep_title_first']."\" onclick=\"return false;\">".$avatarep_avatar['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");

				$avatarep_avatar['avatarep_title_last'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, $avatarep_lastpost['username']);																			
				$avatarep_lastpost['avatarep'] = "<a href=\"". $avatarep_lastpost['profilelink'] . "?action=avatarep_popup\" id=\"tao_member{$myid}\" class=\"tao_member{$uid}\" title=\"".$avatarep_avatar['avatarep_title_last']."\" onclick=\"return false;\">".$avatarep_lastpost['avatarep']."</a>".avatarep_hover_extends($myid,"tao_member");
			}	
			else
			{
				$avatarep_avatar['avatarep_title_first'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, $avatarep_avatar['username']);																						
				$avatarep_avatar['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tal_member{$thread['tid']}\" title=\"".$avatarep_avatar['avatarep_title_first']."\" {$avatar_events}=\"MyBB.popupWindow('". $avatarep_avatar['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$avatarep_avatar['avatarep']."</a>";  			
				$avatarep_avatar['avatarep_title_last'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, $avatarep_lastpost['username']);				
				$avatarep_lastpost['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tao_member{$thread['tid']}\" title=\"".$avatarep_avatar['avatarep_title_last']."\" {$avatar_events}=\"MyBB.popupWindow('". $avatarep_lastpost['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$avatarep_lastpost['avatarep']."</a>";				
			}
		}
		else
		{
			if($mybb->settings['avatarep_menu_events'] == 2)
			{
				$avatarep_avatar['avatarep_title_first'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, $avatarep_avatar['username']);																										
				$avatarep_avatar['avatarep'] = "<a href=\"member.php?uid={$uid}&amp;action=avatarep_popup\" id=\"tal_member{$uid}\" class=\"tal_member{$uid}\" title=\"".$avatarep_avatar['avatarep_title_first']."\" onclick=\"return false;\">".$avatarep_avatar['avatarep']."</a>".avatarep_hover_extends($myid,"tal_member");

				$avatarep_avatar['avatarep_title_last'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, $avatarep_lastpost['username']);								
				$avatarep_lastpost['avatarep'] = "<a href=\"member.php?uid={$uid2}&amp;action=avatarep_popup\" id=\"tao_member{$myid}\" class=\"tao_member{$uid}\" title=\"".$avatarep_avatar['avatarep_title_last']."\" onclick=\"return false;\">".$avatarep_lastpost['avatarep']."</a>".avatarep_hover_extends($myid,"tao_member");
			}
			else
			{
				$avatarep_avatar['avatarep_title_first'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, $avatarep_avatar['username']);												
				$avatarep_avatar['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tal_member{$thread['tid']}\" title=\"".$avatarep_avatar['avatarep_title_first']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$avatarep_avatar['avatarep']."</a>";			
				$avatarep_avatar['avatarep_title_last'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, $avatarep_lastpost['username']);								
				$avatarep_lastpost['avatarep'] = "<a href=\"javascript:void(0)\" id=\"tao_member{$thread['tid']}\" title=\"".$avatarep_avatar['avatarep_title_last']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid2}&amp;action=avatarep_popup', null, true); return false;\">".$avatarep_lastpost['avatarep']."</a>";				
			}
		}
		if($mybb->settings['avatarep_guests'] == 1 && $uid == 0)
		{
			$lang->avatarep_user_alt = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['threadusername']));			
			if($thread['threadusername'] == "")
			{
				$thread['threadusername'] = $post['username'];
			}
			$avatarep_avatar['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$lang->avatarep_user_alt}' />";
		}
		if($mybb->settings['avatarep_guests'] == 1 && $uid2 == 0)
		{
			$avatarep_avatar['avatarep_alt_guest'] = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($thread['lastposter']));			
			$avatarep_lastpost['avatarep'] = "<img src='images/default_avatar.png' class='avatarep_img' alt='{$avatarep_avatar['avatarep_alt_guest']}' />";
		}		
	}
	else
	{
		$avatarep_avatar['avatarep_title_first'] = $lang->sprintf($lang->avatarep_user_alt_thread_first, $avatarep_avatar['username']);		
		$avatarep_avatar['avatarep'] = "<a href=\"". $avatarep_avatar['profilelink'] . "\" id=\"tal_member{$thread['tid']}\" title=\"".$avatarep_avatar['avatarep_title_first']."\">".$avatarep_avatar['avatarep']."</a>";		
		$avatarep_avatar['avatarep_title_last'] = $lang->sprintf($lang->avatarep_user_alt_thread_last, $avatarep_lastpost['username']);				
		$avatarep_lastpost['avatarep'] = 	"<a href=\"". $avatarep_lastpost['profilelink'] . "\" id=\"tao_member{$thread['tid']}\" title=\"".$avatarep_avatar['avatarep_title_last']."\">".$avatarep_lastpost['avatarep']."</a>";	
	}
	$avatarep_avatar['avatarep'] = '<div class="avatarep_fda">' . $avatarep_avatar['avatarep'] . '</div>';
	$avatarep_lastpost['avatarep'] = '<div class="avatarep_fda">' . $avatarep_lastpost['avatarep'] . '</div>';
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
			if(is_array($cache->cache['groups']))
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

function avatarep_private_end()
{
	global $lang, $db, $messagelist, $mybb, $unreadmessages, $readmessages, $avatar_events;
	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_private'] == 0)
    {
        return false;
    }
	$lang->load("avatarep", false, true);	
	if($mybb->settings['avatarep_menu'] == 1){	
		if($mybb->settings['avatarep_menu_events'] == 2)
		{
			$avatar_events = "onmouseover";
			$tids = array();
			foreach(array($messagelist, $unreadmessages, $readmessages) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $tid)
					{
						if(!intval($tid)) continue;
						$tids[] = (int)$tid;
					}
				}
			}
			if(!empty($tids))
			{
				$find = $replace = array();
				foreach($tids as $tid)
				{
					$find[] = "<avatareplt_start[{$tid}]>";
					$replace[] = "<a id=\"pm_member{$tid}\" href";				
				}
			}
			if(isset($messagelist)) $messagelist = str_replace($find, $replace, $messagelist);
			if(isset($readmessages)) $readmessages = str_replace($find, $replace, $readmessages);
			if(isset($unreadmessages)) $unreadmessages = str_replace($find, $replace, $unreadmessages);		
			$tide = array();
			foreach(array($messagelist, $unreadmessages, $readmessages) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $myid)
					{
						if(!intval($myid)) continue;
						$tide[] = (int)$myid;
					}
				}
			}
			if(!empty($tide))
			{
				$find = $replace = array();
				foreach($tide as $myid)
				{
					$find[] = "<avatareplt_end[{$myid}]>";
					$replace[] = avatarep_hover_extends($myid,"pm_member");
				}
			}				
			if(isset($messagelist)) $messagelist = str_replace($find, $replace, $messagelist);
			if(isset($readmessages)) $readmessages = str_replace($find, $replace, $readmessages);
			if(isset($unreadmessages)) $unreadmessages = str_replace($find, $replace, $unreadmessages);		
		}
		else
		{
			$avatar_events = "onclick";		
			$tids = array();
			foreach(array($messagelist, $unreadmessages, $readmessages) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $tid)
					{
						if(!intval($tid)) continue;
						$tids[] = (int)$tid;
					}
				}
			}
			if(!empty($tids))
			{
				$find = $replace = array();
				foreach($tids as $tid)
				{
					$find[] = "<avatareplt_start[{$tid}]>";
					$replace[] = "<!-- Last post avatar v2.9.x start -->";				
				}
			}
			if(isset($messagelist)) $messagelist = str_replace($find, $replace, $messagelist);
			if(isset($readmessages)) $readmessages = str_replace($find, $replace, $readmessages);
			if(isset($unreadmessages)) $unreadmessages = str_replace($find, $replace, $unreadmessages);		
			$tide = array();
			foreach(array($messagelist, $unreadmessages, $readmessages) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $myid)
					{
						if(!intval($myid)) continue;
						$tide[] = (int)$myid;
					}
				}
			}
			if(!empty($tide))
			{
				$find = $replace = array();
				foreach($tide as $myid)
				{
					$find[] = "<avatareplt_end[{$myid}]>";
					$replace[] = "<!-- Last post avatar v2.9.x end-->";				
				}
			}
			if(isset($messagelist)) $messagelist = str_replace($find, $replace, $messagelist);
			if(isset($readmessages)) $readmessages = str_replace($find, $replace, $readmessages);
			if(isset($unreadmessages)) $unreadmessages = str_replace($find, $replace, $unreadmessages);		
		}	
	}
	else
	{
		$avatar_events = "none";		
		$tids = array();
		foreach(array($messagelist, $unreadmessages, $readmessages) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $tid)
				{
					if(!intval($tid)) continue;
					$tids[] = (int)$tid;
				}
			}
		}
		if(!empty($tids))
		{
			$find = $replace = array();
			foreach($tids as $tid)
			{
				$find[] = "<avatareplt_start[{$tid}]>";
				$replace[] = "<!-- Last post avatar v2.9.x start-->";				
			}
		}
		if(isset($messagelist)) $messagelist = str_replace($find, $replace, $messagelist);
		if(isset($readmessages)) $readmessages = str_replace($find, $replace, $readmessages);
		if(isset($unreadmessages)) $unreadmessages = str_replace($find, $replace, $unreadmessages);		
		$tide = array();
		foreach(array($messagelist, $unreadmessages, $readmessages) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $myid)
				{
					if(!intval($myid)) continue;
					$tide[] = (int)$myid;
				}
			}
		}
		if(!empty($tide))
		{
			$find = $replace = array();
			foreach($tide as $myid)
			{
				$find[] = "<avatareplt_end[{$myid}]>";
				$replace[] = "<!-- Last post avatar v2.9.x end-->";				
			}
		}
		if(isset($messagelist)) $messagelist = str_replace($find, $replace, $messagelist);
		if(isset($readmessages)) $readmessages = str_replace($find, $replace, $readmessages);
		if(isset($unreadmessages)) $unreadmessages = str_replace($find, $replace, $unreadmessages);		
	}		
	$users = array();
	foreach(array($messagelist, $unreadmessages, $readmessages) as $content)
	{
		if(!$content) continue;
		preg_match_all('#<avatarep\[([0-9]+)\]#', $content, $matches);
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
			$user['profilelink'] = get_profile_link($user['uid']);
			$user['username'] = htmlspecialchars_uni($user['username']);
			$uid = (int)$user['uid'];
			if($mybb->settings['avatarep_format'] == 1)
			{
				$find[] = ">".$user['userusername']."<";
				$replace[] = ">".format_name($user['userusername'],$user['usergroup'],$user['displaygroup'])."<";						
			}			
			$find[] = "<avatarep[{$user['uid']}]['avatar']>";
			if(empty($user['avatar'])){
				$user['avatar'] = "images/default_avatar.png";
			}else{
				$user['avatar'] = htmlspecialchars_uni($user['avatar']);
			}
			$lang->avatarep_user_alt = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($user['username']));			
			$user['avatar'] = "<img class=\"avatarep_img\" src=\"{$user['avatar']}\" alt=\"{$lang->avatarep_user_alt}\" style=\"display: block;\" />";			
			if($mybb->settings['avatarep_menu'] == 1)
			{
				if(function_exists("google_seo_url_profile"))
				{
					if($mybb->settings['avatarep_menu_events'] == 2)
					{
						$user['avatar'] = "=\"". $user['profilelink'] . "?action=avatarep_popup\" class=\"pm_member{$uid}\" title=\"".$user['username']."\">".$user['avatar']."</a>";						
					}
					else
					{
						$user['avatar'] = "<a href=\"javascript:void(0)\" id=\"pm_member{$myid}\" title=\"".$user['username']."\" {$avatar_events}=\"MyBB.popupWindow('". $user['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";						
					}				
				}
				else
				{
					if($mybb->settings['avatarep_menu_events'] == 2)
					{
						$user['avatar'] = "=\"member.php?uid={$uid}&amp;action=avatarep_popup\" class=\"pm_member{$uid}\" title=\"".$user['username']."\">".$user['avatar']."</a>";
					}
					else
					{
						$user['avatar'] = "<a href=\"javascript:void(0)\" id=\"pm_member{$myid}\" title=\"".$user['username']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";						
					}
				}			
			}
			else
			{
				$user['avatar'] = 	"<a href=\"". $user['profilelink'] . "\" id=\"pm_member{$myid}\" title=\"".$user['username']."\">".$user['avatar']."</a>";
			}			
			$replace[] = $user['avatar'];
		}
		if(isset($messagelist)) $messagelist = str_replace($find, $replace, $messagelist);
		if(isset($readmessages)) $readmessages = str_replace($find, $replace, $readmessages);
		if(isset($unreadmessages)) $unreadmessages = str_replace($find, $replace, $unreadmessages);
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

function avatarep_portal_lt()
{
	global $lang, $db, $mybb, $latestthreads, $avatar_events;
	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_latest_threads'] == 0)
    {
        return false;
    }
	$lang->load("avatarep", false, true);	
	if($mybb->settings['avatarep_menu'] == 1){	
		if($mybb->settings['avatarep_menu_events'] == 2)
		{
			$avatar_events = "onmouseover";
			$tids = array();
			foreach(array($latestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $tid)
					{
						if(!intval($tid)) continue;
						$tids[] = (int)$tid;
					}
				}
			}
			if(!empty($tids))
			{
				$find = $replace = array();
				foreach($tids as $tid)
				{
					$find[] = "<avatareplt_start[{$tid}]>";
					$replace[] = "<a id=\"plt_member{$tid}\" href";				
				}
			}
			if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);	
			$tide = array();
			foreach(array($latestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $myid)
					{
						if(!intval($myid)) continue;
						$tide[] = (int)$myid;
					}
				}
			}
			if(!empty($tide))
			{
				$find = $replace = array();
				foreach($tide as $myid)
				{
					$find[] = "<avatareplt_end[{$myid}]>";
					$replace[] = avatarep_hover_extends($myid,"plt_member");
				}
			}				
			if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);	
		}
		else
		{
			$avatar_events = "onclick";		
			$tids = array();
			foreach(array($latestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $tid)
					{
						if(!intval($tid)) continue;
						$tids[] = (int)$tid;
					}
				}
			}
			if(!empty($tids))
			{
				$find = $replace = array();
				foreach($tids as $tid)
				{
					$find[] = "<avatareplt_start[{$tid}]>";
					$replace[] = "<!-- Last post avatar v2.9.x start-->";				
				}
			}
			if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);	
			$tide = array();
			foreach(array($latestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $myid)
					{
						if(!intval($myid)) continue;
						$tide[] = (int)$myid;
					}
				}
			}
			if(!empty($tide))
			{
				$find = $replace = array();
				foreach($tide as $myid)
				{
					$find[] = "<avatareplt_end[{$myid}]>";
					$replace[] = "<!-- Last post avatar v2.9.x end-->";				
				}
			}
			if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);			
		}	
	}
	else
	{
		$avatar_events = "none";		
		$tids = array();
		foreach(array($latestthreads) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $tid)
				{
					if(!intval($tid)) continue;
					$tids[] = (int)$tid;
				}
			}
		}
		if(!empty($tids))
		{
			$find = $replace = array();
			foreach($tids as $tid)
			{
				$find[] = "<avatareplt_start[{$tid}]>";
				$replace[] = "<!-- Last post avatar v2.9.x start-->";				
			}
		}
		if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);	
		$tide = array();
		foreach(array($latestthreads) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $myid)
				{
					if(!intval($myid)) continue;
					$tide[] = (int)$myid;
				}
			}
		}
		if(!empty($tide))
		{
			$find = $replace = array();
			foreach($tide as $myid)
			{
				$find[] = "<avatareplt_end[{$myid}]>";
				$replace[] = "<!-- Last post avatar v2.9.x end-->";				
			}
		}
		if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);			
	}		
	$users = array();
	foreach(array($latestthreads) as $content)
	{
		if(!$content) continue;
		preg_match_all('#<avatarep\[([0-9]+)\]#', $content, $matches);
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
			$user['profilelink'] = get_profile_link($user['uid']);
			$uid = (int)$user['uid'];			
			$user['username'] = htmlspecialchars_uni($user['username']);			
			if($mybb->settings['avatarep_format'] == 1)	
			{
				$find[] = ">".$user['userusername']."<";
				$replace[] = ">".format_name($user['userusername'],$user['usergroup'],$user['displaygroup'])."<";	
			}
			$find[] = "<avatarep[{$user['uid']}]['avatar']>";
			if(empty($user['avatar']) && $user['uid'] > 0){
				$lang->avatarep_user_alt = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($user['username']));										
				$user['avatar'] = "images/default_avatar.png";				
				$user['avatar'] = "<img class=\"avatarep_img\" src=\"{$user['avatar']}\" alt=\"{$lang->avatarep_user_alt}\" style=\"display: block;\" />";
			}else if(empty($user['avatar']) && $user['uid'] == 0 && $mybb->settings['avatarep_guests'] == 1){
				$lang->avatarep_user_alt = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($user['username']));			
				$user['avatar'] = "images/default_avatar.png";				
				$user['avatar'] = "<img class=\"avatarep_img\" src=\"{$user['avatar']}\" alt=\"{$lang->avatarep_user_alt}\" style=\"display: block;\" />";							
			}else if(empty($user['avatar']) && $user['uid'] == 0 && $mybb->settings['avatarep_guests'] == 0){
				$user['avatar'] = "";								
			}else{
				$lang->avatarep_user_alt = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($user['username']));				
				$user['avatar'] = htmlspecialchars_uni($user['avatar']);
				$user['avatar'] = "<img class=\"avatarep_img\" src=\"{$user['avatar']}\" alt=\"{$lang->avatarep_user_alt}\" style=\"display: block;\" />";							
			}
			if($mybb->settings['avatarep_menu'] == 1)
			{
				if(function_exists("google_seo_url_profile"))
				{
					if($mybb->settings['avatarep_menu_events'] == 2)
					{
						$lang->avatarep_user_alt_thread_first = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($user['username']));						
						$user['avatar'] = "=\"{$user['profilelink']}?action=avatarep_popup\" class=\"plt_member{$uid}\" title=\"".$lang->avatarep_user_alt_thread_first."\">".$user['avatar']."</a>";					
					}
					else
					{
						$lang->avatarep_user_alt_thread_first = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($user['username']));						
						$user['avatar'] = "<a href=\"javascript:void(0)\" class=\"plt_member{$myid}\" title=\"".$lang->avatarep_user_alt_thread_first."\" {$avatar_events}=\"MyBB.popupWindow('". $user['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";
					}
				}
				else					
				{
					if($mybb->settings['avatarep_menu_events'] == 2)
					{
						$lang->avatarep_user_alt_thread_first = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($user['username']));																			
						$user['avatar'] = "=\"member.php?uid={$uid}&amp;action=avatarep_popup\" class=\"plt_member{$uid}\" title=\"".$lang->avatarep_user_alt_thread_first."\">".$user['avatar']."</a>";
					}
					else
					{
						$lang->avatarep_user_alt_thread_first = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($user['username']));													
						$user['avatar'] = "<a href=\"javascript:void(0)\" class=\"plt_member{$myid}\" title=\"".$lang->avatarep_user_alt_thread_first."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";
					}
				}	
			}
			else
			{
				$lang->avatarep_user_alt_thread_first = $lang->sprintf($lang->avatarep_user_alt_thread_first, htmlspecialchars_uni($user['username']));							
				$user['avatar'] = 	"<a href=\"". $user['profilelink'] . "\" class=\"plt_member{$myid}\" title=\"".$lang->avatarep_user_alt_thread_first."\">".$user['avatar']."</a>";
			}		
			$replace[] = $user['avatar'];		
		}
		if(isset($latestthreads)) $latestthreads = str_replace($find, $replace, $latestthreads);	
	}	
}

function avatarep_portal_sb()
{
	global $lang, $db, $mybb, $sblatestthreads, $avatar_events;
	if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_active'] == 1 && $mybb->settings['avatarep_latest_threads'] == 0)
    {
        return false;
    }
	$lang->load("avatarep", false, true);
	if($mybb->settings['avatarep_menu'] == 1){	
		if($mybb->settings['avatarep_menu_events'] == 2)
		{
			$avatar_events = "onmouseover";
			$tids = array();
			foreach(array($sblatestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $tid)
					{
						if(!intval($tid)) continue;
						$tids[] = (int)$tid;
					}
				}
			}
			if(!empty($tids))
			{
				$find = $replace = array();
				foreach($tids as $tid)
				{
					$find[] = "<avatareplt_start[{$tid}]>";
					$replace[] = "<a id=\"plt_member{$tid}\" href";				
				}
			}
			if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);	
			$tide = array();
			foreach(array($sblatestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $myid)
					{
						if(!intval($myid)) continue;
						$tide[] = (int)$myid;
					}
				}
			}
			if(!empty($tide))
			{
				$find = $replace = array();
				foreach($tide as $myid)
				{
					$find[] = "<avatareplt_end[{$myid}]>";
					$replace[] = avatarep_hover_extends($myid,"plt_member");
				}
			}				
			if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);	
		}
		else
		{
			$avatar_events = "onclick";		
			$tids = array();
			foreach(array($sblatestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $tid)
					{
						if(!intval($tid)) continue;
						$tids[] = (int)$tid;
					}
				}
			}
			if(!empty($tids))
			{
				$find = $replace = array();
				foreach($tids as $tid)
				{
					$find[] = "<avatareplt_start[{$tid}]>";
					$replace[] = "<!-- Last post avatar v2.9.x start-->";				
				}
			}
			if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);	
			$tide = array();
			foreach(array($sblatestthreads) as $content)
			{
				if(!$content) continue;
				preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
				if(is_array($matches[1]) && !empty($matches[1]))
				{
					foreach($matches[1] as $myid)
					{
						if(!intval($myid)) continue;
						$tide[] = (int)$myid;
					}
				}
			}
			if(!empty($tide))
			{
				$find = $replace = array();
				foreach($tide as $myid)
				{
					$find[] = "<avatareplt_end[{$myid}]>";
					$replace[] = "<!-- Last post avatar v2.9.x end-->";				
				}
			}
			if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);			
		}	
	}
	else
	{
		$avatar_events = "none";		
		$tids = array();
		foreach(array($sblatestthreads) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatareplt_start\[([0-9]+)\]>#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $tid)
				{
					if(!intval($tid)) continue;
					$tids[] = (int)$tid;
				}
			}
		}
		if(!empty($tids))
		{
			$find = $replace = array();
			foreach($tids as $tid)
			{
				$find[] = "<avatareplt_start[{$tid}]>";
				$replace[] = "<!-- Last post avatar v2.9.x start -->";				
			}
		}
		if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);	
		$tide = array();
		foreach(array($sblatestthreads) as $content)
		{
			if(!$content) continue;
			preg_match_all('#<avatareplt_end\[([0-9]+)\]>#', $content, $matches);
			if(is_array($matches[1]) && !empty($matches[1]))
			{
				foreach($matches[1] as $myid)
				{
					if(!intval($myid)) continue;
					$tide[] = (int)$myid;
				}
			}
		}
		if(!empty($tide))
		{
			$find = $replace = array();
			foreach($tide as $myid)
			{
				$find[] = "<avatareplt_end[{$myid}]>";
				$replace[] = "<!-- Last post avatar v2.9.x end-->";				
			}
		}
		if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);			
	}		
	$users = array();
	foreach(array($sblatestthreads) as $content)
	{
		if(!$content) continue;
		preg_match_all('#<avatarep\[([0-9]+)\]#', $content, $matches);
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
			$user['profilelink'] = get_profile_link($user['uid']);
			$uid = (int)$user['uid'];			
			$user['username'] = htmlspecialchars_uni($user['username']);
			if($mybb->settings['avatarep_format'] == 1)	
			{
				$find[] = ">".$user['userusername']."<";
				$replace[] = ">".format_name($user['userusername'],$user['usergroup'],$user['displaygroup'])."<";	
			}
			$find[] = "<avatarep[{$user['uid']}]['avatar']>";
			$lang->avatarep_user_alt = $lang->sprintf($lang->avatarep_user_alt, htmlspecialchars_uni($user['username']));			
			if(empty($user['avatar']) && $user['uid'] > 0){
				$user['avatar'] = "images/default_avatar.png";
				$user['avatar'] = "<img class=\"avatarep_img\" src=\"{$user['avatar']}\" alt=\"{$lang->avatarep_user_alt}\" style=\"display: block;\" />";							
			}else if(empty($user['avatar']) && $user['uid'] == 0 && $mybb->settings['avatarep_guests'] == 1){
				$user['avatar'] = "images/default_avatar.png";				
				$user['avatar'] = "<img class=\"avatarep_img\" src=\"{$user['avatar']}\" alt=\"{$lang->avatarep_user_alt}\" style=\"display: block;\" />";							
			}else if(empty($user['avatar']) && $user['uid'] == 0 && $mybb->settings['avatarep_guests'] == 0){
				$user['avatar'] = "";								
			}else{
				$user['avatar'] = htmlspecialchars_uni($user['avatar']);
				$user['avatar'] = "<img class=\"avatarep_img\" src=\"{$user['avatar']}\" alt=\"{$lang->avatarep_user_alt}\" style=\"display: block;\" />";							
			}
			if($mybb->settings['avatarep_menu'] == 1)
			{
				if(function_exists("google_seo_url_profile"))
				{
					if($mybb->settings['avatarep_menu_events'] == 2)
					{
						$user['avatar'] = "=\"{$user['profilelink']}?action=avatarep_popup\" class=\"plt_member{$uid}\" title=\"".$user['username']."\">".$user['avatar']."</a>";					
					}
					else
					{
						$user['avatar'] = "<a href=\"javascript:void(0)\" class=\"plt_member{$myid}\" title=\"".$user['username']."\" {$avatar_events}=\"MyBB.popupWindow('". $user['profilelink'] . "?action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";
					}
				}
				else					
				{
					if($mybb->settings['avatarep_menu_events'] == 2)
					{
						$user['avatar'] = "=\"member.php?uid={$uid}&amp;action=avatarep_popup\" class=\"plt_member{$uid}\" title=\"".$user['username']."\">".$user['avatar']."</a>";
					}
					else
					{
						$user['avatar'] = "<a href=\"javascript:void(0)\" class=\"plt_member{$myid}\" title=\"".$user['username']."\" {$avatar_events}=\"MyBB.popupWindow('member.php?uid={$uid}&amp;action=avatarep_popup', null, true); return false;\">".$user['avatar']."</a>";
					}
				}	
			}
			else
			{
				$user['avatar'] = 	"<a href=\"". $user['profilelink'] . "\" class=\"plt_member{$myid}\" title=\"".$user['username']."\">".$user['avatar']."</a>";
			}		
			$replace[] = $user['avatar'];		
		}
		if(isset($sblatestthreads)) $sblatestthreads = str_replace($find, $replace, $sblatestthreads);	
	}	
}
/*
function avatarep_modals_hover($myid, $name)
{
	global $mybb, $lang;
    //Revisar que la opcion este activa
    if($mybb->settings['avatarep_active'] == 0 || $mybb->settings['avatarep_menu'] == 0 && $mybb->settings['avatarep_menu_events'] == 2)
    {
		return false;	
	}
	$lang->load("avatarep", false, true);
	$timeloader = 1000;
	$avatarep_hover = "<div class=\"modal_avatar\" id=\"{$name}_mod{$myid}\"></div>
	<script type=\"text/javascript\">
	$(document).on(\"ready\", function(){
		var NavaT = 0;						
		var myTimer;
		$(\"a#{$name}_member{$myid}\").on(\"click\", function(e){
			e.preventDefault();	
			return false;
		});
		$(\"a#{$name}_member{$myid}\").on(\"mouseover\", function(){
		var Nava = '{$myid}';
		var ID_href = $(this).attr(\"href\");
		var Data = \"id=\" + ID_href;
		console.log(NavaT);
		if(Nava != NavaT)
		{
			myTimer = setTimeout( function()
			{			
				$.ajax({
					url:ID_href,
					data:Data,
					type:\"post\",
					dataType:\"json\",
					beforeSend:function()
					{
						$(\"div#{$name}_mod{$myid}\").css({
							\"display\": \"block\",
							\"margin-top\": \"0px\",
							\"margin-left\": \"0px\",
							\"position\": \"absolute\",
							\"width\": 320														
						});						
						$(\"div#{$name}_mod{$myid}\").fadeIn(\"fast\");										
						$(\"div#{$name}_mod{$myid}\").html(\"<center><img src='images/spinner_big.gif' alt='{$lang->avatarep_retrieving}'><br>{$lang->avatarep_loading}<br></center>\");
					},									
					success:function(res){	
						NavaT = '{$myid}';
						$(\"div#{$name}_mod{$myid} div.modal_avatar\").css(\"display\",\"inline-block\");
						$(\"div#{$name}_mod{$myid}\").html(res);
					}
				});	
			return false;
			}, {$timeloader});
			}
		else
		{
			$(\"div#{$name}_mod{$myid}\").fadeIn(\"slow\");
		}						
		}).on(\"mouseout\", function(){
			if(myTimer)
			clearTimeout(myTimer);				
			$(\"div#{$name}_mod{$myid}\").fadeOut(\"fast\");
			$(this).stop();
		});
	});
</script>
<!-- Last post avatar v2.9.x end-->";
	return $avatarep_hover;
}
*/
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
	$avatar_hover = "<div class=\"modal_avatar\" id=\"{$name}mod{$id}\"></div>
{$avatar_script}
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
			$avatarep =  htmlspecialchars_uni($memprofile['avatar']);
			if($memprofile['avatartype'] == "gravatar")
			{
				$search = "&";
				$replace = "&amp;";
				$avatarep = str_replace($search, $replace, $avatarep);		
			}
			$memprofile['avatartype'] = htmlspecialchars_uni($memprofile['avatartype']);
			$avatarep = "<img src=\"" . $avatarep . "\" alt=\"".$lang->avatarep_user_alt."\" type=\"".$memprofile['avatartype']."\" />";
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