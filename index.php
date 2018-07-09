<?php
  if (!isset($_SERVER['HTTPS']))
  {
    //header('location: https://www.adtalk.services/testtag/contact.php');
    //exit;
  }
  require_once("infiniteeshared.php");
  require_once("class.phpmailer.php");
  SharedInit();
  //$dblink = SharedConnect();
  $contactmsg = "";
  $notification = 0;
  if (isset($_POST['fldName']) && isset($_POST['fldPhone']))
  {
    $name = SharedCleanString($_POST['fldName'], AT_MAXNAME);
    $phone = SharedCleanString($_POST['fldPhone'], AT_MAXPHONE);
    $email = SharedCleanString($_POST['fldEmail'], AT_MAXEMAIL);
    $enquiry = SharedCleanString($_POST['fldEnquiry'], AT_MAXCOMMENTS);
    $body = "<strong>Name:</strong> " . $name . "<br />" .
            "<strong>Mobile:</strong> " . $phone . "<br />" .
            "<strong>Email:</strong> " . $email . "<br />" .
            $enquiry;
    error_log($gConfig['smtp-user']);
    error_log($gConfig['smtp-password']);
    if (SharedSendSimpleHtmlMail($email, $name, $gConfig['admin'], "Infinitee Software", "Infinitee Contact Request", $body))
    {
      $notification = 1;
      $contactmsg = "Thank you for your message. We will contact you ASAP";
    }
      
    else
    {
      $notification = 2;
      $contactmsg = "There was a problem sending your request, please try again later...";

    }
  }
  $fldname = "";
  $flddesc = "";
  $fldidentificationno = "";
  $fldcontact = "";
  $fldphone = "";
  $fldmobile = "";
  $fldemail = "";
  $fldaddress = "";
  $fldcity = "";
  $fldstate = "";
  $fldpostcode = "";
  $fldcountry = "";
  $fldunits = "";
  $fldcurrency = "";
  $flddatecreated = "";
  $flddatemodified = "";
  // Load current values...
//   $dbselect = "select " .
//               "c1.name," .
//               "c1.desc," .
//               "c1.identificationno," .
//               "DATE_FORMAT(c1.datecreated,\"%Y-%m-%d %H:%i\") datecreated," .
//               "DATE_FORMAT(c1.datemodified,\"%Y-%m-%d %H:%i\") datemodified," .
//               "c1.contact," .
//               "c1.phone," .
//               "c1.mobile," .
//               "c1.email," .
//               "c1.address," .
//               "c1.city," .
//               "c1.state," .
//               "c1.postcode," .
//               "c1.country," .
//               "c1.currency," .
//               "c1.units " .
//               "from " .
//               "cust c1 " .
//               "where " .
//               "c1.id=" . $_SESSION['custid'];
//   if ($dbresult = SharedQuery($dbselect, $dblink))
//   {
//     if ($numrows = SharedNumRows($dbresult))
//     {
//       while ($dbrow = SharedFetchArray($dbresult))
//       {
// //        $fldname = $dbrow['name'];
// //        $flddesc = $dbrow['desc'];
// //        $fldidentificationno = $dbrow['identificationno'];
//         $fldcontact = $dbrow['contact'];
//         $fldphone = $dbrow['phone'];
// //        $fldmobile = $dbrow['mobile'];
//         $fldemail = $dbrow['email'];
// //        $fldaddress = $dbrow['address'];
// //        $fldcity = $dbrow['city'];
// //        $fldstate = $dbrow['state'];
//         $fldcountry = $dbrow['country'];
// //        $fldpostcode = $dbrow['postcode'];
// //        $fldunits= $dbrow['units'];
// //        $fldcurrency = $dbrow['currency'];
// //        $flddatecreated = $dbrow['datecreated'];
// //        $flddatemodified = $dbrow['datemodified'];
//         //populateCountries("fldCountry","fldState",$fldcountry,$fldstate);
//       }
//     }
//   }
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">

  <head>
    <?php
      include("meta.php");
    ?>
    <script type="text/javascript">
      $(document).ready(function () {
        var message = "";
        var notification = "";
        message = "<?php if ($contactmsg != "") echo $contactmsg; else echo 123;?>";
        notification = "<?php if ($notification != "") echo $notification; else echo 123;?>";
        console.log(message);
        console.log(notification);
        if (notification == 1) {
          noty({
            text: message,
            type: 'success',
            timeout: 3000
          });
        } else if (notification == 2) {
          noty({
            text: message,
            type: 'error',
            timeout: 3000
          });
        }
        // else if (notification == 3)
        // {
        //     noty({text: message, type: 'success', timeout: 3000});
        // }
        // else if (notification == 4)
        // {
        //     noty({text: message, type: 'error', timeout: 3000});
        // }
        // else if (notification == 5)
        // {
        //     noty({text: message, type: 'success', timeout: 3000});
        // }
        // else if (notification == 6)
        // {
        //     noty({text: message, type: 'error', timeout: 3000});
        // }
      });
    </script>

    <title>Infinitee Software - Contact</title>
  </head>

  <body>
    <img src="images/logo_black_text.png" class="homeImage" alt="logo">
   
    <div class="container">
      <label class="contactTimeLable">
        <?php echo date("l, F j, Y"); ?>
      </label>
      <h2 class="contactTitle mb-2">Please Get in Touch with Us</h2>
      <div class="contactsDIV">
        <!-- <label><?php echo date("l, F j, Y"); ?></label>
          <h2 class="clientTitle">Contact Us</h2> -->
        <div class="entry">
          <form action="index.php" method="post" id="frmContact">
            <table class="table table-borderless">
              <tr>
                <!-- <td align="left" valign="top">Name:</td> -->
                <td colspan="2">
                  <input required id="fldName" name="fldName" type="text" size="20" placeholder="NAME" maxlength="<?php echo AT_MAXNAME; ?>"
                    value="<?php echo SharedPrepareDisplayString($fldcontact); ?>" />
                  <div id='frmContact_fldName_errorloc' class="error_strings"></div>
                </td>
              </tr>
              <tr>
                <!-- <td align="left" valign="top">Mobile:</td> -->
                <td align="left" valign="top" style="width: 50%">
                  <input required title="Invalid Phone number" pattern="^[0-9]{5,}$" id="fldPhone" name="fldPhone" type="text" size="20" style="width: 100%" placeholder="PHONE (Must be at least 5 digits)" maxlength="<?php echo AT_MAXPHONE; ?>"
                    value="<?php echo SharedPrepareDisplayString($fldphone); ?>" />
                  <div id='frmContact_fldPhone_errorloc' class="error_strings"></div>
                </td>
                <td align="left" valign="top" style="width: 50%">
                  <input required title="Invalid Email address" pattern="^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$" id="fldEmail" name="fldEmail" type="text" size="40" style="width: 100%" placeholder="EMAIL" maxlength="<?php echo AT_MAXEMAIL; ?>"
                    value="<?php echo SharedPrepareDisplayString($fldemail); ?>" />
                </td>
              </tr>
              <!-- <tr>
                  <td align="left" valign="top">Email:</td>
                  <td align="left" valign="top"><input id="fldEmail" name="fldEmail" type="text" size="40" maxlength="<?php echo AT_MAXEMAIL; ?>" /></td>
                </tr> -->
              <tr>
                <td>
                  <input id="fldCountry" name="fldCountry" type="text" style="width: 100%" placeholder="Country" value="<?php echo SharedPrepareDisplayString($fldcountry); ?>"
                  />
                </td>
              </tr>
              <tr>
                <!-- <td align="left" valign="top">Enquiry:</td> -->
                <td align="left" valign="top" colspan="2">
                  <textarea id="fldEnquiry" name="fldEnquiry" style="width: 100%" cols="40" rows="5" resize="none" placeholder="ENQUIRY"></textarea>
                </td>
              </tr>
              <tr>

                <td align="left" valign="top" colspan="2">
                  <input id="btnContact" type="submit" style="width:50%" value="Submit" />
                </td>
              </tr>
            </table>
          </form>
          <!-- <script type="text/javascript">
            var frmvalidator = new Validator("frmContact");
            frmvalidator.EnableOnPageErrorDisplay();
            frmvalidator.addValidation("fldName", "req", "Please enter your Name");
            frmvalidator.addValidation("fldName", "alnum_s", "Alpha/numeric and space chars only");
            frmvalidator.addValidation("fldEmail", "email", "Invalid email address format");
            frmvalidator.addValidation("fldPhone", "req", "Please enter your Mobile No.");
            frmvalidator.addValidation("fldPhone",
              "regexp=^[0-9]{10}$|^\(0[1-9]{1}\)[0-9]{8}$|^[0-9]{8}$|^[0-9]{4}[ ][0-9]{3}[ ][0-9]{3}$|^\(0[1-9]{1}\)[ ][0-9]{4}[ ][0-9]{4}$|^[0-9]{4}[ ][0-9]{4}$",
              "Must be in 04xxyyyzzz or xxxxyyyy format");
          </script> -->
        </div>
      </div>
    </div>
  </body>

  </html>