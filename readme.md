Steps to follow:

    1.- Download this new 2.9.7 version for MyBB 1.8.12 and lesser versions of mybb (You have to deactivate old version of plugin before upload new files and then upload and activate to make it works fine).
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
.modal_avatar{display: none;width: auto;height: auto;background: #f0f0f0;border: none;border-radius: 10px;position: absolute;z-index: 99999;}
.modal_avatar_hover{width: auto;height: auto;background: #f0f0f0;border: none;border-radius: 10px;position: absolute;z-index: 99999;}
.tavatar {padding: 0px 10px;text-align: center;}
.tavatar img {max-height: 80px;max-width: 80px;padding: 8px;}
.avatarep_online {border-left: 3px solid #008000;box-shadow: 1px 1px 2px 1px rgba(14, 252, 14, 0.8);border-radius: 3px;opacity: 0.9;}
.avatarep_offline{border-left: 3px solid #FFA500;box-shadow: 1px 1px 2px 1px rgba(252, 165, 14, 0.8);border-radius: 3px;opacity: 0.9;}
.hr {background-color:#FFA500;}
.trow_profile{vertical-align: top;padding-left: 9px;width:340px;color:#424242;}
.trow_profile a{color: #051517;}
.trow_profile a:hover{color: #e09c09;}
.trow_uprofile{min-height:175px;line-height:1.2;}
.trow_uname{font-size:15px;}
.trow_memprofile{font-size:11px;font-weight:bold;}
.trow_status{font-size: 11px;}
.avatarep_img_contributor{padding: 2px;border: 1px solid #D8DFEA;width: 20px;height: 20px;border-radius: 50%;opacity: 0.9;	margin: 2px 5px 0px 2px;float: left;}
.avatarep_img{padding: 3px;border: 1px solid #D8DFEA;width: 30px;height: 30px;border-radius: 50%;opacity: 0.9;margin: 0px 5px 0px 2px;}
.avatarep_fd{float:left;margin: auto;padding: 0px 20px 0px 0px;width:30px;height:40px}
.avatarep_fda,.avatarep_fdl,.avatarep_fdan,.avatarep_fda_mine,.avatarep_fdl_mine{float:left}
.avatarep_fda,.avatarep_fda_mine{margin-right:15px}
.avatarep_fdl_img{width: 20px;height: 20px;border-radius: 50px;position: absolute;margin-left: -35px;margin-top: 25px;border: 1px solid #424242;padding: 2px;}
@media screen and (max-width: 450px){
.tavatar img {height: 30px;width: 30px;padding: 2px;}	
.trow_uname{font-size:12px;}
.avatarep_img_contributor{padding: 2px;border: 1px solid #D8DFEA;width: 19px;height: 19px;border-radius: 50%;opacity: 0.9;	margin: 2px 5px 0px 2px;float: left;}
.avatarep_img{padding: 2px;border: 1px solid #D8DFEA;width: 19px;height: 19px;border-radius: 50%;opacity: 0.9;margin: 0px 5px 0px 2px;}
.avatarep_fd{float:left;margin: auto;padding: 0px 10px 0px 0px;width:20px;height:20px}
.avatarep_fda,.avatarep_fdl,.avatarep_fdan,.avatarep_fda_mine,.avatarep_fdl_mine{float:left}
.avatarep_fda,.avatarep_fda_mine{margin-right:15px}
.avatarep_fdl_img{width: 20px;height: 20px;border-radius: 50px;position: absolute;margin-left: -35px;margin-top: 25px;border: 1px solid #424242;padding: 2px;}
}
```

Save and refresh cache on your explorer.

2.- Avatar is not present on some areas: (Review this templates and his changes only on default theme and default based themes).

Review on your headerincllude template for:

{$avatarep_script}

Template changes:

forumbit_depth1_forum_lastpost
forumbit_depth2_forum_lastpost
 
Review that you have this var:
 
```HTML
<avatarep_uid_[{$lastpost_data['lastposteruid']}]>
```
forumdisplay_thread
 
Review that you have this three vars:
 
```HTML
<avatarep_uid_[{$thread['uid']}]>
{$thread['avatarep']}
<avatarep_uid_[{$thread['lastposteruid']}]>
```

forumdisplay_announcements_announcement
 
Review that you have this var:
 
```HTML
<avatarep_uid_[{$announcement['uid']}]>
```

search_results_posts_post

Review that you have this var:
 
```HTML
<avatarep_uid_[{$post['uid']}]>
```
 
search_results_threads_thread 

Review that you have this two vars:
 
```HTML
<avatarep_uid_[{$thread['uid']}]>
<avatarep_uid_[{$thread['lastposteruid']}]>
```

portal_latestthreads_thread 

Review that you have this var:
 
```HTML
<avatarep_uid_[{$thread['lastposteruid']}]>
```

private_messagebit

Review that you have this var:
 
```HTML
<avatarep_uid_[{$tofromuid}]>
```
private_tracking_readmessage

Review that you have this var:
 
```HTML
<avatarep_uid_[{$readmessage['toid']}]>
```

private_tracking_unreadmessage

Review that you have this var:
 
```HTML
<avatarep_uid_[{$unreadmessage['toid']}]>
```
showthread

Review that you have this var:

```HTML
{$avatarep_thread}
```

headerinclude

Review that you have this var:

```HTML
{$avatarep_script}
```

usercp_latest_subscribed_threads
Review that you have this var:

```HTML
<avatarep_uid_[{$thread['lastposteruid']}]>
```

usercp_latest_threads_threads
Review that you have this var:

```HTML
<avatarep_uid_[{$thread['lastposteruid']}]>
```

You can set anywhere you like vars in templates mentioned above...


For plugin extensions you may use this code:

avatarep_hover_extends($id, $name)

This function at the end of links with the id of items and name for items:

Sample extends mentionme:

$id = (int)$user['id'];
$name = "mention_";

That in the plugin part of mention calls.

Video of referral:

https://mega.nz/#!nh5kFaDa!xIU47jseVelDkBwnkDswfwVJEFflUgkAJtg6IOnT0M8

Instructions to integrate into MentionMe plugin:

https://github.com/WhiteNeo/lastposter-avatar-mybb18x-series/issues/4


And done it !!!!
