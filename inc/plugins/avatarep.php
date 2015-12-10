function forumlist_avatar(&$_f)
{
	global $cache, $db, $fcache, $mybb, $lang, $forum;

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
				if($forum['parentlist'])
				{
					$avatarep_cache[$forum['fid']] = $forum;
					$avatarep_cache[$forum['fid']]['avataruid'] = $forum['lastposteruid'];
					
					$exp = explode(',', $forum['parentlist']);

					foreach($exp as $parent)
					{
						if($parent == $forum['fid']) continue;
						if(isset($avatarep_cache[$parent]) && $forum['lastpost'] > $avatarep_cache[$parent]['lastpost'])
						{
							$lastpost_data = array(
								"lastpost" => $forum['lastpost'],
								"lastpostsubject" => $forum['lastpostsubject'],
								"lastposter" => $forum['lastposter'],
								"lastposttid" => $forum['lastposttid'],
								"lastposteruid" => $forum['lastposteruid']
							);
							$avatarep_cache[$parent]['lastpost'] = $lastpost_data['lastpost'];
							$avatarep_cache[$parent]['avataruid'] = $lastpost_data['lastposteruid']; // Se reemplaza la info de un subforo, por la original...
						}
					}
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
				foreach($users[$user['uid']] as $fid)
				{
					$avatarep_cache[$fid]['avatarep_avatar'] = $avatar;
				}	
			}
		}

		// Aplicamos los cambios! Reemplazando las lineas de código para guardarlas en cache...
		$cache->cache['avatarep_cache'] = $avatarep_cache;	
	}
	
	$_f['avatarep_lastpost'] = $cache->cache['avatarep_cache'][$_f['fid']]['avatarep_avatar'];	
	$_f['uid'] = $_f['avatarep_lastpost']['uid'];
	
	if($mybb->settings['avatarep_menu'] == 1){
		if(function_exists("google_seo_url_profile")){
			$_f['avatarep'] = "</a><a href=\"javascript:void(0)\" id =\"forum_member{$_f['fid']}\" onclick=\"MyBB.popupWindow('". $_f['avatarep_lastpost']['profilelink'] . "?action=avatarep_popup', null, true); return false;\"><span class=\"avatarep_fd\">".$_f['avatarep_lastpost']['avatarep'] . "</span>";
		}
		else{
			$_f['avatarep'] = "</a><a href=\"javascript:void(0)\" id =\"forum_member{$_f['fid']}\" onclick=\"MyBB.popupWindow('member.php?uid={$_f['uid']}&amp;action=avatarep_popup', null, true); return false;\"><span class=\"avatarep_fd\">".$_f['avatarep_lastpost']['avatarep'] . "</span>";
		}
	}else{
		$_f['avatarep'] = "</a><a href=\"". $_f['avatarep_lastpost']['profilelink'] . "\" id =\"forum_member{$_f['fid']}\"><div class=\"avatarep_fd\">".$_f['avatarep_lastpost']['avatarep'] . "</div>";
	}
	if($_f['avatarep_lastpost']['username'] && $mybb->user['uid']){
		$username = '<span class="avatarep_fs">' . format_name($_f['avatarep_lastpost']['username'], $_f['avatarep_lastpost']['usergroup'], $_f['avatarep_lastpost']['displaygroup']) . '</span>';	
		$_f['lastposter'] = $username;
		$_f['lastposter'] .= $_f['avatarep'];
	}else{
		$_f['avatarep'] = "<div class=\"avatarep_fd\"><img src='images/default_avatar.png' class='avatarep_img' alt='{$_f['lastposter']} Avatar' /></div>";
		$_f['lastposter'] .= $_f['avatarep'];		
	}
}
