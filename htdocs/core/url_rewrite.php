<?php
#
# xPyrus - Framework for Community and knowledge exchange
# Copyright (C) 2003-2008 UniHelp e.V., Germany
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, only version 3 of the
# License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program. If not, see http://www.gnu.org/licenses/agpl.txt
#
/*
 * Created on 20.09.2006 by schnueptus
 * sunburner Unihelp.de
 *
 * $Id: url_rewrite.php 6210 2008-07-25 17:29:44Z trehn $
 * $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/url_rewrite.php $
 */

function buildURLHash($user) {
    if ($user == null)
        return null;
    $uid = $user->id;
    $passwd = sha1($user->getPassword());
    return $uid . ',' . $passwd . ',' . sha1($uid . $passwd . URL_SALT);
}

function verifyURLHash($data) {
    $userAuthData = split(',', $data);
    if (count($userAuthData) == 3 && sha1($userAuthData[0] . $userAuthData[1] . URL_SALT) == $userAuthData[2]) {
        $user = UserProtectedModel::getUserById($userAuthData[0]);
        if ($user == null) {
            return null;
        }
        if (sha1($user->getPassword()) == $userAuthData[1]) {
            return $user;
        }
    }
    return null;
}
 
 /**
  * Builds the right URL
  */
function rewrite_forum($param){
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
   if(array_key_exists('editCategoryId',$param)){
   	  $url .= '/forum/cat/'.$param['editCategoryId'].'/edit';
   }
   elseif(array_key_exists('delCategoryId',$param)){
      $url .= '/forum/cat/'.$param['delCategoryId'].'/del';
   }
    elseif(array_key_exists('rePosCategoryId',$param)){
      $url .= '/forum/cat/'.$param['rePosCategoryId'].'/rePos?position='.$param['position'];
   }
   /* if a ForumModel is given */	      
   elseif(array_key_exists('forum',$param)){
      $forum = $param['forum'];
      $page = array_key_exists('page',$param) ? $param['page'] : 1;
      $url .= '/forum/nr/'.$forum->id.'/page/'.$page;
   }
   elseif(array_key_exists('addForum',$param)){
   	  $url .= '/forum?addForum='.$param['addForum'];
   }
   /* if a forumId is given */ 
   elseif(array_key_exists('forumId',$param)){
      $forumId = $param['forumId'];
      if (array_key_exists('rss', $param)) {
        $url .= '/forum/nr/' . $forumId . '/rss';
        $cUser = Session::getInstance()->getVisitor();
        if ($cUser != null) {
            $url .= '?userauth=' . urlencode(buildURLHash($cUser));
        }
      } else {
        $page = array_key_exists('page',$param) ? $param['page'] : 1;
        $url .= '/forum/nr/'.$forumId.'/page/'.$page;
      }
   } 
   elseif(array_key_exists('editForumId',$param)){
      $url .= '/forum/nr/'.$param['editForumId'].'/edit';
   }
   elseif(array_key_exists('delForumId',$param)){
      $url .= '/forum/nr/'.$param['delForumId'].'/del';
   }
   elseif(array_key_exists('rePosForumId',$param)){
      $url .= '/forum/nr/'.$param['rePosForumId'].'/rePos?position='.$param['position'];
   }
   elseif(array_key_exists('editTagsForumId',$param)){
      $url .= '/forum/nr/'.$param['editTagsForumId'].'/editTags';
   }
   /* builds link by a ThreadModel */
   elseif(array_key_exists('thread',$param)){
   	  $thread = $param['thread'];
      $page = array_key_exists('page',$param) ? $param['page'] : 1;     
   	  $url .= '/forum/thread/'.$thread->id.'/page/'.$page;
   }
   elseif(array_key_exists('threadLast',$param)){
   	  $thread = $param['threadLast'];
   	  $url .= '/forum/thread/'.$thread->id.'/last';
   }
   elseif(array_key_exists('delThreadId',$param)){
      $threadId = $param['delThreadId'];       
      $url .= '/forum/thread/'.$threadId.'/del';
   }
   elseif(array_key_exists('editThreadId',$param)){
      $threadId = $param['editThreadId'];       
      $url .= '/forum/thread/'.$threadId.'/edit';
   }
   elseif(array_key_exists('threadCloseStateId',$param)){
   	  $threadId = $param['threadCloseStateId'];       
      $url .= '/forum/thread/'.$threadId.'/close?isClosed='.$param['isClosed'];
   }
   elseif(array_key_exists('threadVisibleStateId',$param)){
      $threadId = $param['threadVisibleStateId'];       
      $url .= '/forum/thread/'.$threadId.'/visible?isVisible='.$param['isVisible'];
   }
   elseif(array_key_exists('threadStickyStateId',$param)){
      $threadId = $param['threadStickyStateId'];       
      $url .= '/forum/thread/'.$threadId.'/sticky?isSticky='.$param['isSticky'];
   }
   /* builds link by a ThreadModel */
   elseif(array_key_exists('threadId',$param)){
      $threadId = $param['threadId'];
      $page = array_key_exists('page',$param) ? $param['page'] : 1 ;     
      $url .= '/forum/thread/'.$threadId.'/page/'.$page;
      if(array_key_exists('rand',$param)){
    	$url .= '/'.$param['rand'];
      }
  }
   /* to show a ThreadEntry */
   elseif(array_key_exists('entryId',$param)){
      $entryId = $param['entryId'];
      $url .= '/forum/entry/'.$entryId;
      $param['anker'] = 'entry'.$entryId;
   }
   elseif(array_key_exists('reportEntryId',$param)){
      $entryId = $param['reportEntryId'];
      $url .= '/forum/entry/'.$entryId.'/report';
      $param['anker'] = 'entry'.$entryId;
   }
   /* to edit a ThreadEntry */
   elseif(array_key_exists('editEntryId',$param)){
   	  $entryId = $param['editEntryId'];
      $url .= '/forum/entry/'.$entryId.'/edit';
  }
   /* to del a ThreadEntry */
   elseif(array_key_exists('delEntryId',$param)){
      $entryId = $param['delEntryId'];
      $url .= '/forum/entry/'.$entryId.'/del';
      if (array_key_exists('confirmation', $param)) {
        $url .= '?deleteConfirmation=yes';
      }
   }
   /* to quote a ThreadEntry */
   elseif(array_key_exists('quoteEntryId',$param)){
      $entryId = $param['quoteEntryId'];
      $url .= '/forum/entry/'.$entryId.'/quote';
   }
   /* to show history of a ThreadEntry */
   elseif(array_key_exists('historyEntryId',$param)){
      $entryId = $param['historyEntryId'];
      $url .= '/forum/entry/'.$entryId.'/history';
   }
   elseif(array_key_exists('tag',$param)){
   	 $tag = $param['tag'];
     $page = array_key_exists('page',$param) ? $param['page'] : 1;
     $url .= '/forum/tag/'.$tag->id.'/page/'.$page;
   }
   elseif(array_key_exists('latest',$param)){
   	  #$url .= '/forum/latest';
      $url .= '/forum/latest';
      return $url;
  }
  elseif(array_key_exists('rss',$param)){
      #$url .= '/forum/latest';
      $url .= '/forum/latest/rss';
      return $url;
  }
  elseif(array_key_exists('threadAboId', $param)){
   	    if(array_key_exists('remove', $param)){
   	    	$url .= '/index.php?mod=forum&method=ForumAbo&threadId='.$param['threadAboId'].'&remove=true';
   	    }
        else{
            $url .= '/index.php?mod=forum&method=ForumAbo&threadId='.$param['threadAboId'];
        }
  } elseif(array_key_exists('search',$param)){
      $url .= '/forum/search';
  } else if (array_key_exists('marketplace',$param)){
      $url .= '/forum#marketplace';
  } else{ 
      $url .= '/forum';
  }
  
  /* adds the anker */
  if(array_key_exists('anker',$param)){
  	$url = $url .'#' . $param['anker'];
  }
  
  return $url;
    
} 

function rewrite_userinfo($param) {
	if (!array_key_exists('user', $param)) {
		die ('no user for userinfo given');
	}
    $user = $param['user'];
    $diaryPage = null;
    $gbPage = null;
    
    if (array_key_exists('diarypage', $param)) {
        $diaryPage = $param['diarypage'];
    }
    if (array_key_exists('gbpage', $param)) {
        $gbPage = $param['gbpage'];
    }    

    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
    // if we have only a string and not a user object
    // try to get suitable model
    if (is_string($user) and $user != null) {
        // check for external users
        if ($user[-1] == 'e') {
            // TODO: generate proper model/link
            return '';
        }
        // OPTIMIZEME
        // fetching user model here each time
        // shouldn't be too expensive because
        // it ought to be cached
        $user = UserProtectedModel::getUserById((int) $user);
    }
    
    if ($user == null) {
        return '';
    }
    
    if ($user->id == null) {
        return '';
    }
    
    // TODO: external users
    $url .= '/user/' . rawurlencode($user->getUsername());
    
    if (array_key_exists('delDiaryEntryId', $param)) {
        $page = empty($diaryPage) ? 1 : $diaryPage;
        $url .= '/diaryentry/' . $param['delDiaryEntryId'].','.$page.'/del';
    }
    elseif (array_key_exists('prepDiaryEntryId', $param)) {
        $page = empty($diaryPage) ? 1 : $diaryPage;
        $url .= '/diaryentry/' . $param['prepDiaryEntryId'].','.$page.'/edit';
    }
    elseif (array_key_exists('addDiaryEntry', $param)) {
        $url .= '/diary/new';
    }
    elseif (array_key_exists('editDiaryEntry', $param)) {
        $page = empty($diaryPage) ? 1 : $diaryPage;
        $url .= '/diaryentry/'.$param['editDiaryEntry'].','.$page.'/edit';
    }
    elseif (array_key_exists('linkDiaryEntryId', $param)) {
        $url .= '/diaryentry/' . $param['linkDiaryEntryId'];
    }    
    elseif (array_key_exists('quoteGBEntryId', $param)) {
        $url .= '/gbentry/' . $param['quoteGBEntryId'].'/quote';
    }
    elseif (array_key_exists('prepCommentGBEntryId', $param)) {
        $page = empty($gbPage) ? 1 : $gbPage;
        $url .= '/gbentry/' . $param['prepCommentGBEntryId'].','.$page.'/comment';
    }
    elseif (array_key_exists('delGBEntryId', $param)) {
        $page = empty($gbPage) ? 1 : $gbPage;
        $url .= '/gbentry/' . $param['delGBEntryId'].','.$page.'/del';
    }
    elseif (array_key_exists('prepGBEntryId', $param)) {
        $page = empty($gbPage) ? 1 : $gbPage;
        $url .= '/gbentry/' . $param['prepGBEntryId'].','.$page.'/edit';
    }
    elseif (array_key_exists('addGBEntry', $param)) {
        $url .= '/gb/new';
    }
    elseif (array_key_exists('editGBEntry', $param)) {
        $page = empty($gbPage) ? 1 : $gbPage;
        $url .= '/gbentry/'.$param['editGBEntry'].','.$page.'/edit';
    }
    elseif (array_key_exists('linkGBEntryId', $param)) {
        $url .= '/gbentry/' . $param['linkGBEntryId'];
    } else if (array_key_exists('reportGBEntryId', $param)) {
        $url .= '/reportgb/' . $param['reportGBEntryId'];
    } else if (array_key_exists('reportDiaryEntryId', $param)) {
        $url .= '/reportdiary/' . $param['reportDiaryEntryId'];
    } else if (array_key_exists('reverseFriendlist', $param)) {
        $url .= '/reversefriendlist';
    }
    
    else if ($diaryPage != null) {
        $url .= '/diary/' . $diaryPage;
    }
    else if ($gbPage != null) {
        $url .= '/gb/' . $gbPage;
    }
    if (array_key_exists('tab', $param)) {
        $url .= '?tabpane=' . $param['tab'];
    } else if (array_key_exists('gbfilter', $param)) {
        $url .= '?gbfilter=' . $param['gbfilter'];
    } else if (array_key_exists('diaryfilter', $param)) {
        $url .= '?diaryfilter=' . $param['diaryfilter'];
    }

    return $url;
}

function rewrite_group($param) {
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
   if(array_key_exists('groupId', $param) && !array_key_exists('remove', $param) && !array_key_exists('rights', $param) && !array_key_exists('add', $param)){
   	    $url .= '/orgas/' . $param['groupId'];
   } else if(array_key_exists('group', $param)){
        $group = $param['group'];
        $url .= '/orgas/' . $group->id . '_' . rawurlencode($group->name);
   } else if(array_key_exists('applicationId', $param)){
        $url .= '/orgas/'.$param['applicationId'].'/application';
   } else if(array_key_exists('leaveId', $param)){
        $url .= '/orgas/'.$param['leaveId'].'/leave';
   }else if(array_key_exists('groupToEdit', $param)){
        $url .= '/orgas/' . $param['groupToEdit'] . '/edit';
   } else if(array_key_exists('editInfo', $param)){
   	    $url .= '/orgas/' . $param['editInfo'] . '/editInfo';
   } else if(array_key_exists('add', $param)){
        $url .= '/orgas/' . $param['groupId'] . '/add/' . $param['add'];
   } else if(array_key_exists('remove', $param)){
        $url .= '/orgas/' . $param['groupId'] . '/remove/' . $param['remove'];
   } else if(array_key_exists('rights', $param)){
        $url .= '/orgas/' . $param['groupId'] . '/rights/' . $param['rights'];
   } else { 
        $url .= '/orgas';
   }

    return $url;
}

function rewrite_blog($param) {
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
    	$url = '';
    }
    
    // if no owner is given, assume the link goes to the
    // blogosphere
    if (!array_key_exists('owner', $param)) {
        $url .= '/blog';
    } else if ($param['owner'] instanceof UserModel) {
        $url .= '/blog/user/' . rawurlencode($param['owner']->getUsername());
    } else if ($param['owner'] instanceof GroupModel) {
    	$url .= '/blog/orgas/' . $param['owner']->id . '_' . rawurlencode($param['owner']->name);
    }

    if (array_key_exists('entry', $param)) {
        if (array_key_exists('edit',$param)) {
            $url .= '/admin/post/' . $param['entry']->id;
        } else if (!array_key_exists('trackback', $param)) {
            $url .= '/archives/' . $param['entry']->id;
        } else {
        	$url .= '/trackback/' . $param['entry']->id;
        }
        
        if (array_key_exists('comments', $param)) {
            $url .= '#comments';
        }
        
        if (array_key_exists('delComment',$param)) {
        	$url .= '/comments/' . $param['delComment']->id. '/del';
        }
        if (array_key_exists('delTrackback',$param)) {
            $url .= '/trackbacks/' . $param['delTrackback']->id. '/del';
        }
    } else if (array_key_exists('comment', $param)) {
        $url .= '/archives/' . $param['comment']->blogEntry->id . '#c' . $param['comment']->id;
    }
    if (array_key_exists('category', $param) and $param['category'] != null) {
		$url .= '/category/' . $param['category']->id;
	}
    
    // convert timestamp format of date into array
    if (!empty($param['archive_date'])) {
    	$param['date'] = explode('-', date('Y-m', $param['archive_date']));
    } else if (!empty($param['archive_date_day'])) {
        $param['date'] = explode('-', date('Y-m-d', $param['archive_date_day']));
    }

    if (array_key_exists('date', $param) and $param['date'] != null) {
        $url .= '/archives/date/' . $param['date'][0] . '/' . $param['date'][1];
        if (count($param['date']) == 3) {
        	$url .= '/' . $param['date'][2];
        }
    }
    if (array_key_exists('page', $param)) {
        $url .= '/page/' . $param['page'];
    }
    if (array_key_exists('feed', $param)) {
        $url .= '/feeds/index.' . $param['feed'];
    } else if (array_key_exists('commentFeed', $param)) {
        $url .= '/feeds/comments.' . $param['commentFeed'];
    }
    if (array_key_exists('admin', $param)) {
        $url .= '/admin';
        if ($param['admin'] == 'category') {
            $url .= '/categories';
        } else if ($param['admin'] == 'misc') {
            $url .= '/misc';
        } else if ($param['admin'] == 'visibility') {
            $url .= '/visibility';
        } else {
            $url .= '/post';
        }
    }
    if (array_key_exists('create', $param)) {
        $url .= '/create';
    }

    return $url;
}
 
function rewrite_pm($param){
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
    if(!empty($param['out'])){
        $url .= '/pm/outbox';
        if(!empty($param['page'])){
        	$url .= '/' . $param['page'];
        }
    }elseif(!empty($param['new'])){
        $url .= '/pm/new';
        if(!empty($param['receivers'])){
            $url .= '/to_'. rawurlencode($param['receivers']);
        }
        if(!empty($param['caption'])){
            $url .= '/caption_'. rawurlencode($param['caption']);
        }
    }elseif(!empty($param['submit'])){
        $url .= '/pm/submit';	
    }elseif(!empty($param['newcourse'])){
        $url .= '/pm/new/course/'.$param['newcourse'];
    }elseif(!empty($param['pm'])){
        $url .= '/pm/'.$param['pm']->id;
    }elseif(!empty($param['pmId'])){
        $url .= '/pm/'.$param['pmId'];
    }elseif(!empty($param['fwd'])){
        $url .= '/pm/'.$param['fwd']->id.'/fwd';  
    }elseif(!empty($param['quote'])){
        $url .= '/pm/'.$param['quote']->id.'/quote';    
    }elseif(!empty($param['del'])){
        $page = empty($param['page']) ? 1 : $param['page'];
        $url .= '/pm/'.$param['del']->id.','.$page.'/del'; 
    }elseif(!empty($param['dels'])){
        $page = empty($param['page']) ? 1 : $param['page'];
        $url .= '/pm/'.$param['dels']->id.','.$page.'/dels'; 
    }else{
        $page = empty($param['page']) ? 1 : $param['page'];
        $url .= '/pm/inbox/' . $page;
    }
    
    $queryGET = array();
    if (!empty($param['sent'])) {
        $queryGET[] = 'sent=1';
    }
    
    if (count($queryGET) > 0) {
        $url .= '?' . implode('&', $queryGET);
    }
    
    return $url;
}

function rewrite_course($param) {   
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
	if (array_key_exists('course', $param)) {
		if (array_key_exists('showFiles', $param)) {
			$url .='/course/' . $param['course']->id . '/files';
			if (array_key_exists('order', $param) && $param['order'] != '') {
				$order = explode(';', $param['order']);
				$url .= '_' . $order[0] .  $order[1];
			}
			if (array_key_exists('page', $param)) {
                $url .= '-' . $param['page'];
            }
		}else{
			$url .='/course/' . $param['course']->id;
		}
	}elseif(array_key_exists('courseFileLatest', $param)){
		   $url .= '/course/file/latest';
    }elseif(array_key_exists('addCourseFile', $param)){
	       //$url .= '/course/' . $param['addCourseFile'] . '/files/add';
           $url .= '/course/' . $param['addCourseFile'] . '/files';
	
    }elseif(array_key_exists('courseFile', $param)){
            $url .= '/course/file/' . $param['courseFile']->id;
    }elseif(array_key_exists('courseFileEdit', $param)){
            $url .= '/course/file/' . $param['courseFileEdit']->id .'?edit=1';
    }elseif(array_key_exists('courseId', $param)){
            $url .= '/course/' . $param['courseId'];
    }elseif(array_key_exists('getFile', $param)){
    	    $url .= '/course/file/' . $param['getFile']->id . '/get';
    }elseif(array_key_exists('rateFile', $param)){
            $url .= '/course/file/' . $param['rateFile']->id . '/rate';
    }elseif(array_key_exists('courseList',$param)){
    	    $url .= '/course/home/'.$param['courseList'];
    }elseif(array_key_exists('couseUserFiles',$param)){
    	    $url .= '/user/' . rawurlencode($param['couseUserFiles']->getUsername()) . '/coursefiles';
    }else{
    	    $url .= '/course';
    }
    return $url;
}

function rewrite_usermanagement($param){
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
    if (!empty($param['features'])){
        $url .= '/user/' . rawurlencode($param['features']->getUsername()) . '/features';
    } else if (!empty($param['boxes'])){
        $url .= '/user/' . rawurlencode($param['boxes']->getUsername()) . '/boxes';
    } else if (!empty($param['profile'])){
        $url .= '/user/' . rawurlencode($param['profile']->getUsername()) . '/profile';
    } else if (!empty($param['friendlist'])){
        $url .= '/user/' . rawurlencode($param['friendlist']->getUsername()) . '/friendlist';
    } else if (!empty($param['contactData'])){
        $url .= '/user/' . rawurlencode($param['contactData']->getUsername()) . '/contactdata';
    } else if (!empty($param['privacy'])){
        $url .= '/user/' . rawurlencode($param['privacy']->getUsername()) . '/privacy';
    } else if (!empty($param['courses'])){
        $url .= '/user/' . rawurlencode($param['courses']->getUsername()) . '/courses';
    } else if (!empty($param['newuser'])){
        $url .= '/newuser';
    } else if (!empty($param['usersearch'])){
        $url .= '/usersearch';
    } else if (!empty($param['activate'])){
        $url .= '/activate/' . $param['activate'];
    } elseif (!empty($param['logout'])){
		$url .= '/logout';
    } elseif (!empty($param['delete'])){
        $url .= '/user/'. rawurlencode($param['delete']->getUsername()) . '/del';
    }
    if (array_key_exists('edit',$param) and $param['edit'] == true){
        $url .= '/edit';
    }
    return $url;
}

function rewrite_admin($param){
	$adminBaseURL = '/i_am_god';
	if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
    if(array_key_exists('user', $param)){
    	$url .= '/user/' . rawurlencode($param['user']->getUsername()) . '/edit';
    } else if (!empty($param['features'])){
        $url = '/user/' . rawurlencode($param['features']->getUsername()) . '/features/edit';
    } else if (!empty($param['boxes'])){
        $url = '/user/' . rawurlencode($param['boxes']->getUsername()) . '/boxes/edit';
    } else if (!empty($param['profile'])){
        $url = '/user/' . rawurlencode($param['profile']->getUsername()) . '/profile/edit';
    } else if (!empty($param['friendlist'])){
        $url = '/user/' . rawurlencode($param['friendlist']->getUsername()) . '/friendlist/edit';
    } else if (!empty($param['contactData'])){
        $url = '/user/' . rawurlencode($param['contactData']->getUsername()) . '/contactdata/edit';
    } else if (!empty($param['privacy'])){
        $url = '/user/' . rawurlencode($param['privacy']->getUsername()) . '/privacy/edit';
    } else if (!empty($param['courses'])){
        $url = '/user/' . rawurlencode($param['courses']->getUsername()) . '/courses/edit';
    } else if (!empty($param['rights'])){
        $url = '/user/' . rawurlencode($param['rights']->getUsername()) . '/rights/edit';
    } else if (!empty($param['warnings'])){
        $url = '/user/' . rawurlencode($param['warnings']->getUsername()) . '/warnings/edit';
    } else if (!empty($param['newuser'])){
        $url = $adminBaseURL . '/newuser';
    } else if (!empty($param['newguest'])){
        $url = $adminBaseURL . '/newguest';
    } else if (!empty($param['purgeusers'])){
        $url = $adminBaseURL . '/purgeusers';
        if (array_key_exists('purgeIds', $param)) {
            $url .= '?purgeIds=' . $param['purgeIds'];
        }
    } else if (!empty($param['roles'])){
        $url = $adminBaseURL . '/roles';
    } else if (!empty($param['role'])){
        $url = $adminBaseURL . '/role/' . $param['role']->id ;
        if(!empty($param['del'])){
        	$url .= '/del';
        }else if(!empty($param['delok'])){
            $url .= '/delok';
        }else if(!empty($param['edit'])){
            $url .= '/edit';
        }else if(!empty($param['add'])){
            $url .= '/add';
        }
    } else if (!empty($param['groups'])){
        $url = $adminBaseURL . '/groups';    
    } else if (!empty($param['group'])){
        $url = $adminBaseURL . '/group/' . $param['group']->id ;
        if(!empty($param['del'])){
            $url .= '/del';
        }else if(!empty($param['delok'])){
            $url .= '/delok';
        }else if(!empty($param['edit'])){
            $url .= '/edit';
        }else if(!empty($param['groupRights'])){
            $url .= '/rights';    
        }else if(!empty($param['add'])){
            $url .= '/add';
        }
    } elseif (!empty($param['studyPaths'])){
		$url .= $adminBaseURL . '/study/edit';
    } elseif (!empty($param['tags'])){
		$url .= $adminBaseURL . '/tags/edit';
    } elseif (!empty($param['coursesEdit'])){
		$url .= $adminBaseURL . '/courses/edit';
    } elseif (!empty($param['coursesMerge'])){
        $url .= '/index.php?mod=i_am_god&method=coursesMerge';
    } elseif (!empty($param['editGB'])){
		$url .= '/user/'.rawurlencode($param['editGB']->getUsername()) . '/editGB';
    } elseif (!empty($param['editDiary'])){
		$url .= '/user/'.rawurlencode($param['editDiary']->getUsername()) . '/editDiary';
    } elseif (!empty($param['showAllEntriesByAuthor'])){
		$url .= '/user/'.rawurlencode($param['showAllEntriesByAuthor']->getUsername()) . '/showAllEntriesByAuthor';
    } elseif (!empty($param['editFiles'])){
		$url .= $adminBaseURL . '/editFiles';
    } elseif (!empty($param['editUserFiles'])){
		$url .= '/user/'.rawurlencode($param['editUserFiles']->getUsername()) . '/editFiles';
    } elseif (!empty($param['freeDownloadFile'])){
		$url .= $adminBaseURL . '/freedownload/' . $param['freeDownloadFile'];
    } elseif (!empty($param['deleteFile'])){
		$url .= $adminBaseURL . '/deleteFile/' . $param['deleteFile'];
    } elseif (!empty($param['deleteFileVersion'])){
		$url .= $adminBaseURL . '/deleteFileVersion/' . $param['deleteFileVersion'];
    } elseif (!empty($param['searchEntries'])){
        $url .= $adminBaseURL . '/bigbrother';
    } 
    
    elseif (!empty($param['systemPm'])){
    	$url .= '/index.php?mod=i_am_god&method=writeSystemPM';
        if (!empty($param['targetuser'])){
            $url .= '&user='.rawurlencode($param['targetuser']->getUsername());
        }
        if (!empty($param['bugId'])){
        	$url .= '&mantisBug='.$param['bugId'];
        }
        if (!empty($param['toAll'])){
        	$url .= '&toAll=1';
        }
        if (!empty($param['toOnline'])){
            $url .= '&toOnline=1';
        }
    }
	else{
    	$url .= $adminBaseURL;
    }
    
    return $url;
}

function rewrite_mantis($param){
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
    $url .= '/support';
    if (!empty($param['type'])){
        $url .= '/' . $param['type'];
    }else{
    	if (!empty($param['reportentry'])){
            $url .= '/' . F_SOURCE_REPORT_ENTRY;
        }
        elseif (!empty($param['errorreport'])){
            $url .= '/' . F_SOURCE_ERROR_REPORT;
        }
        elseif (!empty($param['delentry'])){
            $url .= '/' . F_SOURCE_DELETE_ENTRY;
        }
        elseif (!empty($param['generall'])){
            $url .= '/' . F_SOURCE_GENERAL_QUERY;
        }
        elseif (!empty($param['feature'])){
            $url .= '/' . F_SOURCE_FEATURE_REQUEST;
        }
        elseif (!empty($param['changeusername'])){
            $url .= '/' . F_SOURCE_CHANGE_USERNAME;
        }
        elseif (!empty($param['changeuni'])){
        	$url .= '/' . F_SOURCE_CHANGE_UNI;
        }
        elseif (!empty($param['changebirthday'])){
            $url .= '/' . F_SOURCE_CHANGE_BIRTHDAY;
        }
        elseif (!empty($param['delaccount'])){
            $url .= '/' . F_SOURCE_DELETE_ACCOUNT;
        }
        elseif (!empty($param['addgroup'])){
            $url .= '/' . F_SOURCE_ADD_ME_TO_GROUP;
        }
        elseif (!empty($param['removegroup'])){
            $url .= '/' . F_SOURCE_DELETE_ME_FROM_GROUP;
        }
        elseif (!empty($param['foundgroup'])){
            $url .= '/' . F_SOURCE_FOUND_GROUP;
        }
        elseif (!empty($param['missingcourse'])){
            $url .= '/' . F_SOURCE_MISSING_COURSE;
        }
        elseif (!empty($param['misc'])){
            $url .= '/' . F_SOURCE_MISC;
        }
        elseif (!empty($param['unknown'])){
            $url .= '/' . F_SOURCE_UNKNOWN;
        }
        elseif (!empty($param['calender'])){
        	$url .= '/' . F_SOURCE_CALENDER;
        }
    }
    return $url;
}

function rewrite_help($param){    
    
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    if (array_key_exists('helpTextLink', $param)) {
        /*
         * Notice, we want at the moment no Link to the FAQ or to the help, becouse it is not finisht
         * schnueptus (22.06.2007)
         */
        
        return '';
        $url .= '/help/faq';
        $url .= '?page='.$param['option'];
    } else if (array_key_exists('faq', $param)) {
        $url .= '/help/faq';
    } else if (array_key_exists('formatcode', $param)) {
        $url .= '/help/formatcode';
    }
    
    return $url;
}

function rewrite_user_online($param){
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
	if (array_key_exists('sortByAge', $param)){
		$url .= '/user_online/sortByAge';
	}
	elseif (array_key_exists('sortByGender', $param)){
		$url .= '/user_online/sortByGender';
	}
	elseif (array_key_exists('sortByStatus', $param)){
		$url .= '/user_online/sortByStatus';
	}
	elseif (array_key_exists('sortByCourse', $param)){
		$url .= '/user_online/sortByCourse';
	}
	elseif (array_key_exists('sortByUsername', $param)){
		$url .= '/user_online/sortByUsername';
	}
	else{//default
		$url .= '/user_online/sortByUsername';
	}
	return $url;
}

function rewrite_index($param){
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
    if (array_key_exists('imprint', $param)) {
        $url .= '/imprint';
    } else if (array_key_exists('chat', $param)) {
        $url .= '/chat';
    } else if (array_key_exists('privacy', $param)) {
        $url .= '/privacy';
    } else if (array_key_exists('termsOfUse', $param)) {
        $url .= '/terms_of_use';
    } else if (array_key_exists('toolbarDownload', $param)) {
		$cUser = Session::getInstance()->getVisitor();
		if ($cUser->isRegularLocalUser()) {
			$url .= '/toolbar/unihelp_toolbar-' . $cUser->id . sha1($cUser->getUsername()) . '-' . TOOLBAR_VERSION . '.xpi';
		} else {
			$url .= '/toolbar/unihelp_toolbar-' . TOOLBAR_VERSION . '.xpi';
		}
    } else if (array_key_exists('toolbar', $param)) {
        $url .= '/toolbar';
    } else if (array_key_exists('events', $param)) {
        $url .= '/events';
        if (array_key_exists('add', $param)) {
            $url .= '/add';
        }
        if (array_key_exists('eventId', $param)) {
            $url .= '/' . $param['eventId'];
            if (array_key_exists('delete', $param)) {
                $url .= '/del';
            } else if (array_key_exists('edit', $param)) {
                $url .= '/edit';
            }
        }
        
        if (array_key_exists('weeks', $param)) {
            $url .= '?weeks=' . $param['weeks'];
        }
        if (array_key_exists('ical', $param)) {
            $url .= '.ics';
        }
    } else {
        $url .= '/home';
    }
    return $url;
}

function rewrite_box_functions($param){
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
	
	if (array_key_exists('box', $param)){
		$url .= '/'.$param['box'];
	
		if (array_key_exists('instance', $param)){
			$url .= '/instance/'.$param['instance'];
		}
		if (array_key_exists('close', $param)){
			$url .= '/close';
		}
		elseif (array_key_exists('minimize', $param)){
			$url .= '/minimize';
		}
		elseif (array_key_exists('maximize', $param)){
			$url .= '/maximize';
		}
		else{
			$url .= '/maximize';
		}
	}
	else{//default
		$url .= '';
	}
	return $url;
}


function rewrite_sports($param) {
    if (array_key_exists('extern', $param)) {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
    } else {
        $url = '';
    }
    
	if (array_key_exists('home', $param)) {
        $url .= '/sports';
    } else if (array_key_exists('soccerBet', $param)) {
        $url .= '/sports/bet/' . $param['soccerBet']->id;
    } else if (array_key_exists('soccerBetUser', $param)) {
        $url .= '/user/' . rawurlencode($param['soccerBetUser']->getUsername()) . '/bet/' . $param['tournament']->id;
	} else if (array_key_exists('soccerBetStats', $param)) {
        $url .= '/sports/stat' . $param['soccerBetStats']->id . '.svg';
    } else if (array_key_exists('soccerBetNotifyWinner', $param)) {
        $url .= '/index.php?mod=sports&method=soccerBetNotifyWinner&uid=' . $param['user']->id . '&rank=' . $param['rank'];
	} else if (array_key_exists('soccerBetAdmin', $param)) {
        $url .= '/i_am_god/sports/' . $param['soccerBetAdmin']->id;
    } else if (array_key_exists('soccerBetRanking', $param)) {
        $url .= '/sports/bet/' . $param['soccerBetRanking']->id . '/ranking';
        if (array_key_exists('page', $param)) {
            $url .= '?page=' . $param['page'];
        }
    }
    return $url;
}

?>
