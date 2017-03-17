Steps to follow:

    1.- Download this new 2.9.2 developers version for MyBB 1.8.8 and lesser versions of mybb (You have to deactivate old version of plugin before upload new files and then upload and activate to make it works fine).
    2.- Enable plugin.
    3.- Go to styles and templates and create avatarep.css stylesheet.
    4.- Make changes on style and templates to set visual customization.
    5.- Config adjustments of plugin opts.
    6.- Edit annother things like modal or contents.
    7.- Enjoy !!!
	

1.- We have to go to styles and templtes, go to select your style and on styles go to create new. 
Set avatarep.css as name, select global, and select write my own code. (Copy and paste this content)

```CSS
// Avatars Menu //
.modal_avatar{
	display: none;
	width: auto;
	height: auto;
	background: #f0f0f0;
	border: none;
	border-radius: 10px;
	position: absolute;
	z-index: 99999;
}

.tavatar {
	padding: 0px 10px;
	text-align: center;
}
	
.tavatar img {
    height: 80px;
    width: 80px;
    padding: 8px;
}

.avatarep_online {
	border: 1px solid #008000;
	box-shadow: 1px 1px 4px 2px rgba(14, 252, 14, 0.8);
	border-radius: 5px;
	opacity: 0.8;
}

.avatarep_offline{
    border: 1px solid #FFA500;
	box-shadow: 1px 1px 4px 2px rgba(252, 165, 14, 0.8);
	border-radius: 5px;
	opacity: 0.8;
}

.hr {
	background-color:#089;
}

.trow_profile{
	vertical-align: top;
	padding-left: 9px;
	width:340px;
	color:#424242;
}

.trow_profile a{
	color: #051517;
}

.trow_profile a:hover{
	color: #e09c09;
}

.trow_uprofile{
	min-height:175px;
	line-height:1.2;
}

.trow_uname{
	font-size:15px;
}

.trow_memprofile{
	font-size:11px;
	font-weight:bold;
}

.trow_status{
	font-size: 11px;
}

.avatarep_img{
    padding: 3px;
	border: 1px solid #D8DFEA;
    width: 40px;
	height: 40px;
	border-radius: 50%;
	opacity: 0.9;
}

```

Save and refresh cache on your explorer.

2.- Template addittions:

        forumbit_depth2_forum_lastpost
        $forum['avatar'] - Ruta de la imagen
        $forum['avatarep'] - Código preformateado
            
        forumdisplay_announcements_announcement
        $anno_avatar['avatar'] - Ruta de la imagen
        $anno_avatar['avatarep'] - Código preformateado

       forumdisplay_thread, search_results_threads_thread, search_results_posts_post (only the first and seccond vars are permited)
        $avatarep_avatar['avatar'] - creador del tema (Ruta de la imagen)
        $avatarep_lastpost['avatar'] - ultimo envío (Ruta de la imagen)
        $avatarep_avatar['avatar'] - creador del tema (Código preformateado)
        $avatarep_lastpost['avatar'] - ultimo envío (Código preformateado)

        avatarep_popup
        $memprofile['avatar'] - Ruta de la imagen
        $memprofile['avatarep'] - Código preformateado
        
        showthread
        $avatarep['avatar'] - plugin SEO
        
        private_messagebit
        <avatareplt_start[{$message['pmid']}]><avatarep[{$tofromuid}]['avatar']><avatareplt_end[{$message['pmid']}]>
	private_tracking_readmessage
        <avatareplt_start[{$readmessage['pmid']}]><avatarep[{$readmessage['toid']}]['avatar']><avatareplt_end[{$readmessage['pmid']}]>
	private_tracking_unreadmessage
        <avatareplt_start[{$unreadmessage['pmid']}]><avatarep[{$unreadmessage['toid']}]['avatar']><avatareplt_end[{$unreadmessage['pmid']}]>
        
        portal_latestthreads_thread
        <avatarep[{$thread['lastposteruid']}]['avatar']>
