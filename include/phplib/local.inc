<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998-2000 NetUSE AG
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: local.inc,v 1.7 2006/12/12 22:20:30 dnaber Exp $
 *
 */ 
include_once(dirname(__FILE__)."/../config.php");

 class DB_Thesaurus extends DB_Sql {
  var $Host     = DB_HOST;
  var $Database = DB_NAME;
  var $User     = DB_USER;
  var $Password = DB_PASSWORD;
}

class Thesaurus_CT_Sql extends CT_Sql {
  var $database_class = "DB_Thesaurus";          ## Which database to connect...
  var $database_table = "active_sessions"; ## and find our session data in this table.
}

class Thesaurus_Session extends Session {
  var $classname = "Thesaurus_Session";

  var $cookiename     = "";                ## defaults to classname
  var $magic          = "Foobarstuff";      ## ID seed
  var $mode           = "cookie";          ## We propagate session IDs with cookies
  var $fallback_mode  = "";
  var $lifetime       = 10080;  #60*24*7               ## 0 = do session cookies, else minutes
  var $that_class     = "Thesaurus_CT_Sql"; ## name of data storage container
  var $gc_probability = 5;  
}

class Thesaurus_User extends User {
  var $classname = "Thesaurus_User";

  var $magic          = "Blahblubbblah";     ## ID seed
  var $that_class     = "Thesaurus_CT_Sql"; ## data storage container
}

class Thesaurus_Auth extends Auth {
  var $classname      = "Thesaurus_Auth";

  #var $nobody    = true;

  var $lifetime       =  10080;	# see $lifetime above

  var $database_class = "DB_Thesaurus";
  var $database_table = "auth_user";
  
  function auth_loginform() {
    global $sess;
    global $_PHPLIB;

    include($_PHPLIB["libdir"] . "loginform.html");
  }
  
  function auth_validatelogin() {

    $uid = false;
    if(isset($_POST['username'])) {
      $this->auth["uname"] = $_POST['username'];    ## This provides access for "loginform.ihtml"
    
      $query = sprintf("SELECT user_id, perms ".
                               "        FROM %s ".
                               "       WHERE username = '%s' ".
                               "       AND STRCMP(password, '%s') = 0 ".
                               "       AND blocked = 0",
                        	$this->database_table,
                        	addslashes($_POST['username']),
                        	addslashes($_POST['password']));
      $this->db->query($query);
      $found_login = 0;
      while($this->db->next_record()) {
    	$uid = $this->db->f("user_id");
    	$this->auth["perm"] = $this->db->f("perms");
    	$found_login = 1;
      }
      # dnaber, 2006-12-12: remeber date of login
      if ($found_login == 1) {
        $query = sprintf("UPDATE auth_user SET last_login = '%s' WHERE user_id = '%s'",
        	date("Y-m-d H:i:s"),
	        $this->localmyaddslashes($uid));
        $this->db->query($query);
      }
    }
    return $uid;
  }
  
  function localmyaddslashes($st) { 
	if( get_magic_quotes_gpc() == 1 ) {
		return $st;
	} else {
		return addslashes($st); 
	}
  }
  
}

class Thesaurus_Default_Auth extends Thesaurus_Auth {
  var $classname = "Thesaurus_Default_Auth";
  
  var $nobody    = true;
}

class Thesaurus_Challenge_Auth extends Auth {
  var $classname      = "Thesaurus_Challenge_Auth";

  var $lifetime       =  1;

  var $magic          = "Bimsalabimfoo";  ## Challenge seed
  var $database_class = "DB_Thesaurus";
  var $database_table = "auth_user";

  function auth_loginform() {
    global $sess;
    global $challenge;
    global $_PHPLIB;
    
    $challenge = md5(uniqid($this->magic));
    $sess->register("challenge");
    
    include($_PHPLIB["libdir"] . "crloginform.ihtml");
  }
  
  function auth_validatelogin() {
    global $username, $password, $challenge, $response;

    if(isset($username)) {
      $this->auth["uname"]=$username;        ## This provides access for "loginform.ihtml"
    }
    $this->db->query(sprintf("select user_id,perms,password ".
                "from %s where username = '%s'",
                          $this->database_table,
                          addslashes($username)));

    while($this->db->next_record()) {
      $uid   = $this->db->f("user_id");
      $perm  = $this->db->f("perms");
      $pass  = $this->db->f("password");
    }
    $exspected_response = md5("$username:$pass:$challenge");

    ## True when JS is disabled
    if ($response == "") {
      if ($password != $pass) {
        return false;
      } else {
        $this->auth["perm"] = $perm;
        return $uid;
      }
    }
    
    ## Response is set, JS is enabled
    if ($exspected_response != $response) {
      return false;
    } else {
      $this->auth["perm"] = $perm;
      return $uid;
    }
  }
}

##
## Thesaurus_Challenge_Crypt_Auth: Keep passwords in md5 hashes rather 
##                           than cleartext in database
## Author: Jim Zajkowski <jim@jimz.com>

class Thesaurus_Challenge_Crypt_Auth extends Auth {
  var $classname      = "Thesaurus_Challenge_Crypt_Auth";

  var $lifetime       =  1;

  var $magic          = "BlooFrobozzica";  ## Challenge seed
  var $database_class = "DB_Thesaurus";
  var $database_table = "auth_user_md5";

  function auth_loginform() {
    global $sess;
    global $challenge;
    
    $challenge = md5(uniqid($this->magic));
    $sess->register("challenge");
    
    include("crcloginform.ihtml");
  }
  
  function auth_validatelogin() {
    global $username, $password, $challenge, $response;

    $this->auth["uname"]=$username;        ## This provides access for "loginform.ihtml"
    
    $this->db->query(sprintf("select user_id,perms,password ".
                "from %s where username = '%s'",
                          $this->database_table,
                          addslashes($username)));

    while($this->db->next_record()) {
      $uid   = $this->db->f("user_id");
      $perm  = $this->db->f("perms");
      $pass  = $this->db->f("password");   ## Password is stored as a md5 hash
    }
    $exspected_response = md5("$username:$pass:$challenge");

    ## True when JS is disabled
    if ($response == "") {
      if (md5($password) != $pass) {       ## md5 hash for non-JavaScript browsers
        return false;
      } else {
        $this->auth["perm"] = $perm;
        return $uid;
      }
    }
    
    ## Response is set, JS is enabled
    if ($exspected_response != $response) {
      return false;
    } else {
      $this->auth["perm"] = $perm;
      return $uid;
    }
  }
}

class Thesaurus_Perm extends Perm {
  var $classname = "Thesaurus_Perm";
  
  var $permissions = array(
                            "user"       => 1,
                            "author"     => 2,
                            "editor"     => 4,
                            "supervisor" => 8,
                            "admin"      => 16
                          );

  function perm_invalid($does_have, $must_have) {
    global $perm, $auth, $sess;
    global $_PHPLIB;
    
    include($_PHPLIB["libdir"] . "perminvalid.ihtml");
  }
}

?>
