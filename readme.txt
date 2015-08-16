Steps to follow:

    1.- Downloaad this repo from github on 2.8.3 version only, on other versions may change due to core changes.
    2.- Enable plugin.
    3.- Go to styles and templates and verify avatarep.css exists and got content.
    4.- Make changes on style and templates to set visual customization.
    5.- Config adjustments of plugin opts.
    6.- Edit annother things like modal or contents.
    7.- Enjoy !!!
	
We don't have avatarep.css, then go to styles and create a new stylesheet called avatarep.css, inside this go to advanced mode and paste this content.

[code]
/* POPUP MENU*/
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
 
.avatarep_fs{
                position: relative; 
                font-size: 12px;
}
 
.avatarep_fd{
                margin-left: -5px;
                margin-top: -40px;
                position: relative;
                float: left;
                padding: 0 5px;
}

[/code] 

Save and refresh cache on your explorer.

Template changes:

forumbit_depth2_forum_lastpost:
 
Change all content to this:
 
[code]
<table border="0">
  <tr>
    <td width="20%" align="left" valign="middle"><span class="smalltext">{$lastpost_profilelink}</span></td>
    <td width="80%" align="left" valign="top">
<span class="smalltext">
<a href="{$lastpost_link}" title="{$full_lastpost_subject}"><strong>{$lastpost_subject}</strong></a>
<br />{$lastpost_date} {$lastpost_time}</span>
   </td>
  </tr>
</table>
[/code]


forumbit_depth2_forum:
 
Change all content to this:

[code]
<tr>
<td class="{$bgcolor}" align="center" width="5%"><span class="forum_status forum_{$lightbulb['folder']} ajax_mark_read" title="{$lightbulb['altonoff']}" id="mark_read_{$forum['fid']}"></span></td>
<td class="{$bgcolor}" width="50%">
<strong><a href="{$forum_url}">{$forum['name']}</a></strong>{$forum_viewers_text}<div class="smalltext">{$forum['description']}{$modlist}{$subforums}</div>
</td>
<td width="10%" class="{$bgcolor}" align="center" style="white-space: nowrap">{$threads}{$unapproved['unapproved_threads']}</td>
<td width="10%" class="{$bgcolor}" align="center" style="white-space: nowrap">{$posts}{$unapproved['unapproved_posts']}</td>
<td width="25%" class="{$bgcolor}" align="left" style="white-space: nowrap">{$lastpost}</td>
</tr>
[/code]


forumdisplay_showthread
 
Change all content to this:
 
[code]
<tr class="inline_row">
            <td align="center" class="{$bgcolor}{$thread_type_class}" width="2%"><span class="thread_status {$folder}" title="{$folder_label}">&nbsp;</span></td>
            <td align="center" class="{$bgcolor}{$thread_type_class}" width="2%">{$avatarep_avatar['avatarep']}</td>
            <td class="{$bgcolor}{$thread_type_class}">
                        {$attachment_count}
                        <div>
                                   <span>{$icon}{$prefix} {$gotounread}{$thread['threadprefix']}<span class="{$inline_edit_class} {$new_class}" id="tid_{$inline_edit_tid}"><a href="{$thread['threadlink']}">{$thread['subject']}</a></span>{$thread['multipage']}</span>
                                   <div class="author smalltext">{$thread['profilelink']}</div>
                        </div>
            </td>
            <td align="center" class="{$bgcolor}{$thread_type_class}"><a href="javascript:MyBB.whoPosted({$thread['tid']});">{$thread['replies']}</a>{$unapproved_posts}</td>
            <td align="center" class="{$bgcolor}{$thread_type_class}">{$thread['views']}</td>
            {$rating}
            <td class="{$bgcolor}{$thread_type_class}" style="white-space: nowrap; text-align: left;">
        <table border="0">
         <tr>
         <td>{$avatarep_lastpost['avatarep']}</td>
         <td>
                        <span class="lastpost smalltext">{$lastpostdate} {$lastposttime}<br />
                        <a href="{$thread['lastpostlink']}">{$lang->lastpost}</a>: {$lastposterlink}</span>
        </td>
        </tr>
        </table>
            </td>
{$modbit}
</tr>
[/code]


forumdisplay_announcements_announcement
 
Change all content to this:

[code] 
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
[/code]
 
 
search_results_posts_post
Change all content to this:

[code]
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
[/code]
 

search_results_threads_thread cambiar todo por
Change all content to this:

[code] 
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
            <table border"0">
                <tr>
                                                           <td width="2%">
                                                                       {$avatarep_lastpost['avatarep']}
                                                           </td>
                                                           <td>
                                                                       <span class="smalltext">
                                                                                  {$lastpostdate}<br />
                                                                                  <a href="{$thread['lastpostlink']}">{$lang->lastpost}</a>: {$lastposterlink}
                                                                       </span>
                                                           </td>
                                               </tr>
                                   </table>
            </td>
            {$inline_mod_checkbox}
</tr>
[/code]


And done it !!!!

don't copy [code] tags tht's only to reference purposes you can copy and paste this guide on your forum and see contents to do more easy the steps on this usersguide...


Sample of this guide on spanish only

http://soportemybb.es/Tema-Estilizar-plugin-avatar-en-temas-y-foros-2-8-3?pid=10157#pid10157

There you can get pdf guide with images for an entire customization on default theme to make more beauty your forums....