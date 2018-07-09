<?php
  $gConfig['dbname'] = "remedy";
  $gConfig['dbserver'] = "localhost:3306";
  $gConfig['dbport'] = 3306;
  $gConfig['dbusername'] = "remedy";
  $gConfig['dbpwd'] = "pocketremedy9900";
  $gConfig['dberrormsg'] = "";
  // $gConfig['smtp-user'] = "fafa.lai@infinitee.software";
  $gConfig['smtp-user'] = "noreply@infinitee.software";
  $gConfig['smtp-password'] = "ishout\$00";
  // ishout$00
  $gConfig['smtp-host'] = "mail.infinitee.software";
  $gConfig['admin'] = 'fafa.lai@infinitee.software';

  // Set some handy globals...
  date_default_timezone_set("Australia/Melbourne");
  ini_set("auto_detect_line_endings", true);

  define('AT_UNKNOWN', 0);

  define('AT_MAXNAME', 50);
  define('AT_MAXPWD', 50);
  define('AT_MAXCODE', 50);
  define('AT_MAXADDRESS', 50);
  define('AT_MAXEMAIL', 200);
  define('AT_MAXURL', 200);
  define('AT_MAXPHONE', 20);
  define('AT_MAXCOMMENTS', 2000);
  define('AT_MAXPOSTCODE', 4);
  define('AT_MAXSTATE', 3);
  define('AT_MAXBIGINT', 20);
  define('AT_MAXDATA', 100);
  define('AT_MAXDATE', 20);
  define('AT_MAXDECIMAL', 16);
  define('AT_MAXFILENAME', 200);
  define('AT_MAXNOTE', 200);
  define('AT_MAXENQUIRY', 500);

  define('AT_CMDNONE', 0);
  define('AT_CMDLIST', 1);
  define('AT_CMDMODIFY', 2);
  define('AT_CMDCREATE', 3);
  define('AT_CMDDELETE', 4);
  define('AT_CMDFIND', 5);
  define('AT_CMDFINDRESULTS', 6);
  define('AT_CMDCUSTOM1', 7);
  define('AT_CMDCUSTOM2', 8);
  define('AT_CMDPRINT1', 9);
  define('AT_CMDPRINT2', 10);
  define('AT_CMDPRINT3', 11);
  define('AT_CMDPRINT4', 12);
  define('AT_CMDPRINT5', 13);
  define('AT_CMDCOPY', 14);
  define('AT_CMDAPPROVE', 15);
  define('AT_CMDCOMPLETE', 16);
  define('AT_CMDHOLD', 17);
  define('AT_CMDUNHOLD', 18);
  define('AT_CMDLAST', 19);

  define('AT_STATUS_PENDING', 0);
  define('AT_STATUS_APPROVED', 1);
  define('AT_STATUS_COMPLETED', 2);
  define('AT_STATUS_HOLD', 3);

  define('AT_GOOGLESTATICMAP_APIKEY', 'AIzaSyDK6rHUSfuOGD1PnBykm_K2m1UmeLXJMJQ');

  define('REMEDY_FOLDER_PHOTOS', "photos/");

  $isphp5 = false;
  $dblink = null;

  if (version_compare(phpversion(), "5.5.0", "<"))
    $isphp5 = true;


  function SharedConnect()
  {
    // $dblink = false;
    // try
    // {
    //   global $gConfig;
    //   $dblink = mysql_connect($gConfig['dbserver'], $gConfig['dbusername'], $gConfig['dbpwd']);
    //   if ($dblink)
    //   {
    //     if (!mysql_select_db($gConfig['dbname'], $dblink))
    //     {
    //       mysql_close($dblink);
    //       $dblink = false;
    //     }
    //   }
    // }

    // catch (Exception $e)
    // {
    //   error_log("Exception in " . $e->getFile() . " line " . $e->getLine() . ": " . $e->getMessage());
    // }

    // return ($dblink);
    global $isphp5;

    $dblink = false;

    try
    {
      global $gConfig;

      if ($isphp5)
      {
        $dblink = mysql_connect($gConfig['dbserver'], $gConfig['dbusername'], $gConfig['dbpwd']);
        if ($dblink)
        {
          if (!mysql_select_db($gConfig['dbname'], $dblink))
          {
            mysql_close($dblink);
            $dblink = false;
          }
        }
      }
      else
      {
        $dblink = mysqli_init();
        if (!mysqli_real_connect($dblink, $gConfig['dbserver'], $gConfig['dbusername'], $gConfig['dbpwd'], $gConfig['dbname'], $gConfig['dbport']))
          $dblink = false;
      }
    }

    catch (Exception $e)
    {
      error_log("Exception in " . $e->getFile() . " line " . $e->getLine() . ": " . $e->getMessage());
    }

    return ($dblink);
  }

  function SharedInit()
  {
    session_start();
    if (!isset($_SESSION['loggedin']))
    {
      $_SESSION['loggedin'] = 0;    // user id from db
      $_SESSION['username'] = '';   // username from db
      $_SESSION['userid'] = '';     // userid from db
      $_SESSION['admin'] = 0;       // non zero if is admin
      $_SESSION['custid'] = 0;      // user's cust id
      $_SESSION['custuuid'] = '';   // cust's uuid
      $_SESSION['connectid'] = 0;   // connection id if any
      $_SESSION['price'] = 0;       // price level
      $_SESSION['numberUsers'] = 0; //number of users for this cust
    }
  }

  function SharedCleanString($text, $maxlen)
  {
    if ($text != "")
    {
      $text = trim($text);
      if ($text != "")
      {
        if ($maxlen < 1)
          $maxlen = 1;
        $text = substr($text, 0, $maxlen);
      }
    }
    return ($text);
  }

  function SharedLogin()
  {
    try
    {
      global $gConfig;
      $gConfig['dberrormsg'] = "";
      $dateday= date("Y-m-d H:i:s");
      if ($_SESSION['loggedin'] == 0)
      {
        if (isset($_POST['fldUid']) && isset($_POST['fldPwd']))
        {
          if ($dblink = SharedConnect())
          {
            $username = SharedCleanString($_POST['fldUid'], AT_MAXNAME);
            $password = SharedCleanString($_POST['fldPwd'], AT_MAXPWD);
            $dbselect = "select " .
                        "u1.id," .
			                  "u1.uid," .
                        "u1.name," .
                        "u1.cust_id," .
                        "u1.admin," .
                        "u1.numberUsers," .
                        "c1.uuid cust_uuid " .
                        "FROM " .
                        "users u1 left join cust c1 on (u1.cust_id=c1.id) " .
                        "where " .
                        "u1.uid=" . SharedNullOrQuoted($username,100, $dblink) .
                        "and " .
                        "u1.pwd=" . SharedNullOrQuoted(SharedCleanString($password, 50), 50, $dblink) .
                        "and " .
                        "u1.active=1";
            error_log($dbselect);
            if ($dbresult = SharedQuery($dbselect, $dblink))
            {
              if ($numrows = SharedNumRows($dbresult))
              {
                $gConfig['dberrormsg'] = "No such user.";
                $rc = false;
                while ($dbrow = SharedFetchArray($dbresult))
                {
                  $_SESSION['loggedin'] = intval($dbrow['id']);           // ID of logged in user
                  $_SESSION['username'] = $dbrow['name'];                 // Name of logged in user
                  $_SESSION['userid'] = $dbrow['uid'];                    // UserId in user
                  $_SESSION['admin'] = intval($dbrow['admin']);           // Is user an admin?
                  $_SESSION['custid'] = intval($dbrow['cust_id']);        // User's cust id
                  $_SESSION['custuuid'] = $dbrow['cust_uuid'];            // Cust's uuid
                  $_SESSION['numberUsers'] = intval($dbrow['numberUsers']);  //get the accpeted number of users
                  $rc = true;
                }
                if ($_SESSION['loggedin'] != 0)
                {
                  // Record the connection details...
                  $address = $_SERVER['REMOTE_ADDR'] . ":" . $_SERVER['REMOTE_PORT'];
                  $dbinsert = "insert into " .
                              "connections " .
                              "(" .
                              "users_id," .
                              "address" .
                              ") " .
                              "values " .
                              "(" .
                              $_SESSION['loggedin'] . "," .
                              "'" . $address . "'" .
                              ")";
                  if (SharedQuery($dbinsert, $dblink))
                    $_SESSION['connectid'] = SharedGetInsertId($dblink);
                }
              }
              else
              {
                $gConfig['dberrormsg'] = "No login information found";
                error_log($gConfig['dberrormsg']);
                $rc = false;
              }
            }
            else
            {
              $gConfig['dberrormsg'] = "Unable to query database for login credentials";
              error_log($gConfig['dberrormsg']);
              $rc = false;
            }
          }
          else
          {
            $gConfig['dberrormsg'] = "Unable to connect to database";
            error_log($gConfig['dberrormsg']);
            $rc = false;
          }
        }
        else
          $rc = false;
      }
      else
        $rc = true;
    }

    catch (Exception $e)
    {
      error_log("Exception in " . $e->getFile() . " line " . $e->getLine() . ": " . $e->getMessage());
    }

    return ($rc);
  }

  function SharedLogout()
  {
    try
    {
      if ($_SESSION['connectid'] != 0)
      {
        if ($dblink = SharedConnect())
        {
          $dbupdate = "update " .
                      "connections " .
                      "set " .
                      "dateexpired=CURRENT_TIMESTAMP " .
                      "where " .
                      "id=" . $_SESSION['connectid'];
          SharedQuery($dbupdate, $dblink);
        }
      }
      unset($_SESSION['loggedin']);
      unset($_SESSION['username']);
      unset($_SESSION['admin']);
      unset($_SESSION['custid']);
      unset($_SESSION['custuuid']);
      unset($_SESSION['connectid']);
      unset($_SESSION['userid']);

      session_unset();
      session_destroy();
      session_write_close();
    }

    catch (Exception $e)
    {
      error_log("Exception in " . $e->getFile() . " line " . $e->getLine() . ": " . $e->getMessage());
    }
  }

  function SharedUnInit()
  {
  }

  function SharedIsLoggedIn()
  {
    if (isset($_SESSION['loggedin']) && ($_SESSION['loggedin'] == 0))
      return (false);

    return (true);
  }

  function SharedGetParentScriptName()
  {
    $file = basename($_SERVER["SCRIPT_NAME"]);
    return ($file);
  }

  function SharedIsEqual($s1, $s2)
  {
    $s1 = trim($s1);
    $s2 = trim($s2);
    // If either field blank, ignore (ie. treat as equal)
    if (($s1 == "") || ($s2 == ""))
      $rc = true;
    else
    {
      if (strtoupper($s1) == strtoupper($s2))
        $rc = true;
      else
        $rc = false;
    }
    return ($rc);
  }

  function SharedNormaliseMobile($mobile)
  {
    // Need 614xxYYYzzz format...
    $mobile = trim($mobile);
    $needle = array(" ", "(", ")", "-", ".", "/", "+");
    $mobile = str_replace($needle, "", $mobile);
    if (substr($mobile, 0, 2) == "04")
    {
      // Remove leading "0", so have 4xxYYYzzz
      $mobile = "61" . substr($mobile, 1, strlen($mobile) - 1);
    }
    else
    {
      if (substr($mobile, 0, 1) == "4")
        $mobile = "61" . $mobile;
    }
    return ($mobile);
  }

  function SharedIsMobileNo($mobile)
  {
    $m = SharedNormaliseMobile($mobile);
    if (substr($m, 0, 3) == "614")
      return ($m);
    return (false);
  }

  function SharedQuery($dbquery, $dblink)
  {
    global $isphp5;

    $dbresult = null;

    if ($isphp5)
      $dbresult = mysql_query($dbquery, $dblink);
    else
      $dbresult = mysqli_query($dblink, $dbquery);

    return ($dbresult);
  }

  function SharedNumRows($dbresult)
  {
    global $isphp5;

    $numrows = false;

    if ($isphp5)
      $numrows = mysql_num_rows($dbresult);
    else
      $numrows = mysqli_num_rows($dbresult);

    return ($numrows);
  }

  function SharedGetInsertId($dblink)
  {
    global $isphp5;

    $id = null;

    if ($isphp5)
      $id = mysql_insert_id($dblink);
    else
      $id = mysqli_insert_id($dblink);

    return ($id);
  }

  function SharedFetchArray($dbresult)
  {
    global $isphp5;

    $dbrow = false;

    if ($isphp5)
      $dbrow = mysql_fetch_array($dbresult, MYSQL_ASSOC);
    else
      $dbrow = mysqli_fetch_array($dbresult);

    return ($dbrow);
  }

  function SharedNullOrQuoted($val, $len,$dblink)
  {
    // $rc = "null";
    // $val = trim($val);
    // if ($val != "")
    //   $rc = "'" . mysql_real_escape_string($val, $dblink) . "'";
    // return ($rc);
    global $isphp5;

    $rc = "null";
    $val = trim($val);

    if ($val != "")
    {
      if ($isphp5)
        $rc = "'" . mysql_real_escape_string($val, $dblink) . "'";
      else
        $rc = "'" . mysqli_real_escape_string($dblink, $val) . "'";
    }

    return ($rc);

  }

  function SharedNullOrNum($val, $dblink)
  {
    global $isphp5;

    $rc = "null";
    $val = trim($val);

    if ($val != "")
      $rc = $val;

    return ($rc);
  }

  function SharedNullOrBigInt($val)
  {
    $rc = "null";
    if ($val != 0)
      $rc = $val;
    return ($rc);
  }

  function SharedNullOrDecimal($val)
  {
    $rc = "null";
    if ($val != 0.0)
      $rc = $val;
    return ($rc);
  }

  function SharedGetPrice($val)
  {
    $rc = 0.0;
    try
    {
      if ($val != "")
        $rc = floatval(trim(str_replace("$", "", $val)));
    }

    catch (Exception $e)
    {
    }

    return ($rc);
  }

  function SharedSendHtmlMail($from, $fromName, $to, $toName, $subject, $msg, $cc = "", $ccName = "", $attachment = "")
  {
    $rc = false;
    try
    {
      global $gConfig;
      $mail           = new PHPMailer(true);
      $mail->SMTPAuth = true;
      $mail->Port     = 25;
      $mail->Host     = $gConfig['smtp-host'];
      $mail->Username = $gConfig['smtp-user'];
      $mail->Password = $gConfig['smtp-password'];
      $mail->Subject  = $subject;
      $mail->IsSMTP();
      $mail->MsgHTML($msg);
      $mail->SetFrom($from, $fromName);

      if (is_array($to))
      {
        foreach ($to as $idx=>$t)
          $mail->AddAddress($t, $toName[$idx]);
      }
      else if (is_string($to))
      {
        if ($to != "")
          $mail->AddAddress($to, $toName);
      }

      if (is_array($cc))
      {
        foreach ($cc as $idx=>$c)
          $mail->AddCC($c, $ccName[$idx]);
      }
      else if (is_string($cc))
      {
        if ($cc != "")
          $mail->AddCC($cc, $ccName);
      }

      if (is_array($attachment))
      {
        foreach ($attachment as $a)
          $mail->AddAttachment($a);
      }
      else if (is_string($attachment))
      {
        if ($attachment != "")
          $mail->AddAttachment($attachment);
      }

      $rc = $mail->Send();
    }

    catch (phpmailerException $e)
    {
      error_log($e->errorMessage());
    }

    return ($rc);
  }

  function SharedSendSimpleHtmlMail($from, $fromName, $to, $toName, $subject, $msg, $attachments = "")
  {
    $rc = false;
    try
    {
      global $gConfig;
      $mail            = new PHPMailer(true);
      $mail->SMTPAuth  = true;
      $mail->Port      = 25;
      $mail->MTPSecure = "ssl";
      $mail->Host      = $gConfig['smtp-host'];
      $mail->Username  = $gConfig['smtp-user'];
      $mail->Password  = $gConfig['smtp-password'];
      $mail->Subject   = $subject;
      $mail->IsSMTP();
      $mail->MsgHTML($msg);
      $mail->SetFrom($from, $fromName);
      $mail->AddAddress($to, $toName);

      if (is_array($attachments))
      {
        foreach ($attachments as $attachment)
          $mail->AddAttachment($attachment);
      }
      else if (is_string($attachments))
      {
        if ($attachments != "")
          $mail->AddAttachment($attachments);
      }

      $rc = $mail->Send();
    }

    catch (phpmailerException $e)
    {
      error_log("Exception in " . $e->getFile() . " line " . $e->getLine() . ": " . $e->errorMessage());
    }

    return ($rc);
  }

  function SharedUserNameAvailable($uid, $dblink)
  {
    $avail = true;
    try
    {
      $dateday= date("Y-m-d H:i:s");
      $dbselect = "select " .
                  "u1.id " .
                  "FROM " .
                  "users u1 " .
                  "where " .
                  "u1.uid='" . SharedNullOrQuoted($uid, 20,$dblink) . "' " .
                  "and " .
                  "u1.licexpired>='". $dateday . "' ";
      if ($dbresult = SharedQuery($dbselect, $dblink))
      {
        if ($numrows = SharedNumRows($dbresult))
        {
          while ($dbrow = SharedFetchArray($dbresult))
            $avail = false;
        }
      }
    }

    catch (Exception $e)
    {
      error_log("Exception in " . $e->getFile() . " line " . $e->getLine() . ": " . $e->getMessage());
    }

    return ($avail);
  }

  function SharedPrepareToolTip($txt)
  {
    $tip = addslashes(htmlentities($txt));
    $tip = str_replace("\r\n", "<br />", $tip);
    return ($tip);
  }

  function SharedPrepareDisplayString($txt)
  {
    return (addslashes(htmlspecialchars($txt)));
  }

  function SharedAddEllipsis($txt, $maxlen = 15)
  {
    $result = substr($txt, 0, $maxlen);
    if (strlen($result) < strlen($txt))
      $result .= "...";
    return (SharedPrepareDisplayString($result));
  }

  function SharedGoogleStaticMapUrl($gpslat, $gpslon)
  {
    $url = "http://maps.googleapis.com/maps/api/staticmap?center=" .
           $gpslat . "," .
           $gpslon .
           "&zoom=16&size=512x512&maptype=roadmap&format=png8&markers=color:red%7Clabel:S%7C" .
           $gpslat . "," .
           $gpslon . "&sensor=true&key=" . AT_GOOGLESTATICMAP_APIKEY;
    return ($url);
  }

  function SharedIndentFromPath($path)
  {
    $spaces = intval(substr_count($path, "/"));
    $indent = ($spaces > 0) ? str_repeat("&nbsp;", ($spaces - 1) * 4) : "";
    return ($indent);
  }

  function SharedSendSocketNotification($fields)
  {
      $fieldlist = "";
      $url = "https://www.remedyappserver.com:57900/notifications";

      // URL-ify the data for the POST
      foreach ($fields as $key => $value) {$fieldlist .= $key . "=" . urlencode($value) . "&";}
      rtrim($fieldlist, "&");

      // Open connection
      $ch = curl_init();

      // Set the url, number of POST vars, POST data
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, count($fields));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldlist);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      // Execute post
      $result = curl_exec($ch);

      // Close connection
      curl_close($ch);
      return ($result);
  }
?>
