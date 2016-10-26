Steps to follow:

    1.- Download this new 2.8.8 version for MyBB 1.8.8 and lesser versions of mybb (You have to deactivate old version of plugin before upload new files and then upload and activate to make it works fine).
    2.- Enable plugin.
    3.- Go to styles and templates and verify avatarep.css exists and got content.
    4.- Make changes on style and templates to set visual customization.
    5.- Config adjustments of plugin opts.
    6.- Edit annother things like modal or contents.
    7.- Enjoy !!!
	
KNOWN ISSUES:

1.- We don't have avatarep.css, then go to styles and create a new stylesheet called avatarep.css, inside this go to advanced mode and paste this content.

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

2.- Avatar is not present on some areas: (Review this templates and his changes only on default theme and default based themes).

Template changes:

forumbit_depth2_forum_lastpost:
 
Change all content to this:
 
```HTML
<div style="float:left;">{$forum['avatarep']}</div>
<div>
<span class="smalltext">
	<a href="{$lastpost_link}" title="{$full_lastpost_subject}">{$lastpost_subject}</a> 
	<br />
	{$lastpost_date}
	<br />
	{$lang->by} {$forum['lastposter']}
</span>
</div>
```


forumbit_depth2_forum:
 
Change all content to this:

```HTML
<tr>
<td class="{$bgcolor}" align="center" width="5%"><span class="forum_status forum_{$lightbulb['folder']} ajax_mark_read" title="{$lightbulb['altonoff']}" id="mark_read_{$forum['fid']}"></span></td>
<td class="{$bgcolor}" width="50%">
<strong><a href="{$forum_url}">{$forum['name']}</a></strong>{$forum_viewers_text}<div class="smalltext">{$forum['description']}{$modlist}{$subforums}</div>
</td>
<td width="10%" class="{$bgcolor}" align="center" style="white-space: nowrap">{$threads}{$unapproved['unapproved_threads']}</td>
<td width="10%" class="{$bgcolor}" align="center" style="white-space: nowrap">{$posts}{$unapproved['unapproved_posts']}</td>
<td width="25%" class="{$bgcolor}" align="left" style="white-space: nowrap">{$lastpost}</td>
</tr>
```

forumdisplay_thread
 
Change all content to this:
 
```HTML

<tr class="inline_row">
	<td align="center" class="{$bgcolor}{$thread_type_class}" width="2%"><span class="thread_status {$folder}" title="{$folder_label}">&nbsp;</span></td>
	<td align="center" class="{$bgcolor}{$thread_type_class}" width="2%">{$icon}</td>
	<td class="{$bgcolor}{$thread_type_class}">
		{$attachment_count}
		<div>
<div style="float:left;">{$avatarep_avatar['avatarep']}</div>
<div>
          <span>{$prefix} {$gotounread}{$thread['threadprefix']}<span class="{$inline_edit_class} {$new_class}" id="tid_{$inline_edit_tid}"><a href="{$thread['threadlink']}">{$thread['subject']}</a></span></span>
			<div class="author smalltext">Iniciado por: {$thread['owner']} {$thread['multipage']}</div>
		</div>
</div>
	</td>
	<td align="center" class="{$bgcolor}{$thread_type_class}"><a href="javascript:MyBB.whoPosted({$thread['tid']});">{$thread['replies']}</a>{$unapproved_posts}</td>
	<td align="center" class="{$bgcolor}{$thread_type_class}">{$thread['views']}</td>
	{$rating}
	<td class="{$bgcolor}{$thread_type_class}" style="white-space: nowrap; text-align: right;">
<div style="float:left;">
{$avatarep_lastpost['avatarep']}</div>
<div>
		<span class="lastpost smalltext">{$lastpostdate}<br />
		<a href="{$thread['lastpostlink']}">{$lang->lastpost}</a>:<br />{$thread['lastposter']}</span>
</div>
	</td>
{$modbit}
</tr>

```

forumdisplay_announcements_announcement
 
Change all content to this:

```HTML
<tr>
<td align="center" class="{$bgcolor}" width="2%"><span class="thread_status {$folder}">&nbsp;</span></td>
<td align="center" class="{$bgcolor}" width="2%">{$anno_avatar['avatarep']}</td>
<td class="{$bgcolor} forumdisplay_announcement">
            <a href="{$announcement['announcementlink']}"{$new_class}>{$announcement['subject']}</a>
            <div class="author smalltext">{$announcement['profilelink']}</div>
</td>
<td align="center" class="{$bgcolor} forumdisplay_announcement">-</td>
<td align="center" class="{$bgcolor} forumdisplay_announcement">-</td>
{$rating}
<td class="{$bgcolor} forumdisplay_announcement" style="white-space: nowrap; text-align: right"><span class="smalltext">{$postdate}</span></td>
{$modann}
</tr>
```

search_results_posts_post
Change all content to this:

```HTML
<tr class="inline_row">
            <td align="center" class="{$bgcolor}" width="2%"><span class="thread_status {$folder}">&nbsp;</span>{$icon}&nbsp;</td>
            <td align="center" class="{$bgcolor}" width="2%">{$avatarep_avatar['avatarep']}</td>
            <td class="{$bgcolor}">
                        <span class="smalltext">
                                   {$lang->post_thread} <a href="{$thread_url}{$highlight}">{$post['thread_subject']}</a><br />
                                   {$lang->post_subject} <a href="{$post_url}{$highlight}#pid{$post['pid']}">{$post['subject']}</a>
                        </span><br />
                        <table width="100%"><tr><td><span class="smalltext"><em>{$prev}</em></span></td></tr></table>
            </td>
            <td align="center" class="{$bgcolor}">{$post['profilelink']}</td>
            <td class="{$bgcolor}" >{$post['forumlink']}</td>
            <td align="center" class="{$bgcolor}"><a href="javascript:MyBB.whoPosted({$post['tid']});">{$post['thread_replies']}</a></td>
            <td align="center" class="{$bgcolor}">{$post['thread_views']}</td>
            <td class="{$bgcolor}" style="white-space: nowrap; text-align: center;"><span class="smalltext">{$posted}</span></td>
            {$inline_mod_checkbox}
</tr>
```
 

search_results_threads_thread 
Change all content to this:

```HTML
<tr class="inline_row">
            <td align="center" class="{$bgcolor}" width="2%"><span class="thread_status {$folder}" title="{$folder_label}">&nbsp;</span></td>
            <td align="center" class="{$bgcolor}" width="2%">{$avatarep_avatar['avatarep']}</td>
            <td class="{$bgcolor}">
                        {$attachment_count}
                        <div>
                                   <span>{$icon}{$prefix} {$gotounread}{$thread['threadprefix']}<a href="{$thread_link}{$highlight}" class="{$inline_edit_class} {$new_class}" id="tid_{$inline_edit_tid}">{$thread['subject']}</a>{$thread['multipage']}</span>
                                   <div class="author smalltext">{$thread['profilelink']}</div>
                        </div>
            </td>
            <td class="{$bgcolor}">{$thread['forumlink']}</td>
            <td align="center" class="{$bgcolor}"><a href="javascript:MyBB.whoPosted({$thread['tid']});">{$thread['replies']}</a></td>
            <td align="center" class="{$bgcolor}">{$thread['views']}</td>
            <td class="{$bgcolor}" style="white-space: nowrap">
<div style="float:left;">{$avatarep_lastpost['avatarep']}</div>
<div>
                                                                       <span class="smalltext">
                                                                                  {$lastpostdate}<br />
                                                                                  <a href="{$thread['lastpostlink']}">{$lang->lastpost}</a>: {$lastposterlink}
                                                                       </span>
</div>
            </td>
            {$inline_mod_checkbox}
</tr>
```

For new 2.8.8 versions and better.

portal_latestthreads_thread 
Change all content to this:

```HTML

<tr>
<td class="{$altbg}">
<div style="float:left;"><avatareplt_start[{$thread['tid']}]><avatarep[{$thread['lastposteruid']}]['avatar']><avatareplt_end[{$thread['tid']}]></div>
<div>
<strong><a href="{$mybb->settings['bburl']}/{$thread['threadlink']}">{$thread['subject']}</a></strong>
<span class="smalltext"><br />
<a href="{$thread['lastpostlink']}"><img src="images/jump.png" alt="{$lang->latest_threads_lastpost}" /></a>{$lang->by} {$lastposterlink}<br />
{$lastpostdate}<br />
{$lang->forum} <a href="{$thread['forumlink']}">{$thread['forumname']}</a><br />
<strong>&raquo; </strong>{$lang->latest_threads_replies} {$thread['replies']}<br />
<strong>&raquo; </strong>{$lang->latest_threads_views} {$thread['views']}
</span>
</div>
</td>
</tr>

```

private_messagebit
Change all content to this:

```HTML
<tr>
<td align="center" class="trow1" width="1%"><img src="{$theme['imgdir']}/{$msgfolder}" alt="{$msgalt}" title="{$msgalt}" /></td>
<td align="center" class="trow2" width="1%">{$icon}</td>
<td class="trow1" width="35%">{$msgprefix}<a href="private.php?action=read&amp;pmid={$message['pmid']}">{$message['subject']}</a>{$msgsuffix}{$denyreceipt}</td>
<td align="center" class="trow2"><avatareplt_start[{$message['pmid']}]><avatarep[{$tofromuid}]['avatar']><avatareplt_end[{$message['pmid']}]>{$tofromusername}</td>
<td class="trow1" align="right" style="white-space: nowrap"><span class="smalltext">{$senddate}</span></td>
<td class="trow2" align="center"><input type="checkbox" class="checkbox" name="check[{$message['pmid']}]" value="1" /></td>
</tr>
```

private_tracking_readmessage
Change all content to this:

```HTML
<tr>
<td align="center" class="trow1" width="1%"><img src="{$theme['imgdir']}/old_pm.png" alt="" /></td>
<td class="trow2">{$readmessage['subject']}</td>
<td class="trow1" align="center"><avatareplt_start[{$readmessage['pmid']}]><avatarep[{$readmessage['toid']}]['avatar']><avatareplt_end[{$readmessage['pmid']}]>{$readmessage['profilelink']}</td>
<td class="trow2" align="right"><span class="smalltext">{$readdate}</span></td>
<td class="trow1"><input type="checkbox" class="checkbox" name="readcheck[{$readmessage['pmid']}]" value="1" /></td>
</tr>
```

private_tracking_unreadmessage
Change all content to this:

```HTML
<tr>
<td align="center" class="trow1" width="1%"><img src="{$theme['imgdir']}/new_pm.png" alt="" /></td>
<td class="trow2">{$unreadmessage['subject']}</td>
<td class="trow1" align="center"><avatareplt_start[{$unreadmessage['pmid']}]><avatarep[{$unreadmessage['toid']}]['avatar']><avatareplt_end[{$unreadmessage['pmid']}]>{$unreadmessage['profilelink']}</td>
<td class="trow2" align="right"><span class="smalltext">{$senddate}</span></td>
<td class="trow1"><input type="checkbox" class="checkbox" name="unreadcheck[{$unreadmessage['pmid']}]" value="1" /></td>
</tr>
```

And done it !!!!

Sample of this guide on spanish only for 2.8.5 versions or lesser.

http://soportemybb.es/Tema-Estilizar-plugin-avatar-en-temas-y-foros-2-8-3?pid=10157#pid10157

There you can get pdf guide with images for an entire customization on default theme to make more beauty your forums....
