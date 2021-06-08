<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class Customize extends Model {

    protected $table = 'settings_values';
    protected $softDelete = false;
    public $timestamps = false;
    protected  $AUTOREMINDEREMAIL_SUBJECT_TEMPLATE = "Reminder your scheduled payment";
    protected $AUTOREMINDEREMAILTEMPLATE = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
				<head>
				<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
				</head>
				<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
					<tr><td>[:LOGO:]</td><td></td>
					</tr>
                                        <tr><td colspan=\"2\">Hello [:FIRSTNAME:] [:LASTNAME:]</td></tr>
					<tr><td colspan=\"2\">Your scheduled payment to [:COMPANYNAME:] for [:TOTAL_AMOUNT:] will be processed using your [:PAY_METHOD:] on [:DATE_AUTO:] </td></tr>
					<tr><td colspan=\"2\"><br />Amount $[:AMOUNT:].<br /></td></tr>
					<tr><td colspan=\"2\"><br />Convenience fee $[:CFEE:].<br /></td></tr>
					<tr><td colspan=\"2\"><br />Total $[:TOTAL_AMOUNT:].<br /></td></tr>
					<tr><td colspan=\"2\"><br />If you need to make any changes, please login to your account at [:LOGINLINK].<br /></td></tr>
					<tr><td colspan=\"2\"><br />If you have any questions, please contact our Customer Service team at 866-REVO-411.<br /></td></tr>
					<tr><td colspan=\"2\"><br />Please do not reply to this email message, as this email was sent from a notification-only address.<br /></td></tr>
					</table>
                                        Thank You,<br>[:CONTACTNAME:]<br>[:COMPANYNAME:]
					</body>	</html>";
    protected $INVOICECANCEL_SUBJECT = "Invoice has been cancelled";
    protected $INVOICECANCELMAIL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
				<head>
				<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
				</head>
				<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
					<tr><td>[:LOGO:]</td><td></td>
					</tr>
                                        <tr><td colspan=\"2\">Dear [:FIRSTNAME:] [:LASTNAME:]</td></tr>
					<tr><td colspan=\"2\">This email is to notify that effective today, [:FIRSTNAME:] [:LASTNAME:] has cancelled Invoice [:INVOICE_NUMBER:]</td></tr>
					<tr><td colspan=\"2\"><br />If you need assistance with logging in to your account, resetting your password or setting up new recurring payments, please [:TICKETLINK:].<br /></td></tr>
					</table>
                                        Thank You,<br>[:CONTACTNAME:]<br>[:COMPANYNAME:]
					</body>	</html>";
    protected $AUTOSUCCESSFULEMAIL_SUBJECT="Scheduled Payment was Approved";
    protected $AUTOSUCCESSFULEMAIL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
							<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
							<head><style>td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: black;} </style>
							</head><body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
    <tbody>
        <tr>
            <td>[:LOGO:]</td>
            <td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
        </tr>
        <tr><td colspan=\"2\"><br>For customer service please [:TICKETLINK:].<br></td></tr>
        <tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Result</b></td></tr>
        <tr><td><b>Date:</b></td><td>[:TRANS_DATE:]</td></tr>
        <tr><td><b>Reference #:</b></td><td>[:REFNUM:]</td></tr>
        <tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Details</b></td></tr>
        <tr><td><b>Paying in:</b></td><td>[:DBA_NAME:]</td></tr>
        <tr><td><b>Type:</b></td><td>Sale</td></tr>
        <tr><td><b>Source:</b></td><td>[:SOURCE:]</td></tr>
        <tr><td><b>Account #:</b></td><td>[:ACCOUNTNUMBER:] / [:FIRSTNAME:] [:LASTNAME:]</td></tr>
        <tr><td><b>Created at:</b></td><td>[:STARTDATE:]</td></tr>
        <tr><td><b>Frequency:</b></td><td>[:FREQUENCY:]</td></tr>
        <tr><td><b>Next Payment:</b></td><td>[:NEXTDATE:]</td></tr>
        <tr><td><b>Payment Information:</b></td> <br> <td>[:DESCRIPTION:]</td></tr>
        <tr><td colspan=\"2\">[:DISCLAIMER:]</td></tr>
        <tr><td colspan=\"2\"><p>Remember you can edit or cancel your scheduled autopayments in the payor portal. If you are not registered user, please proceed to create an account to get access to this feature.</p></td></tr>
        <tr><td colspan=\"2\"><p style=\"color: #666666; font-size: 12px\"> Please do not reply to this email message, as this email was sent from a notification-only address.</p><p>Revo Payments Inc.</p></td></tr>
    </tbody>
</table></body></html>";
    protected $SUCCESSFULEMAIL_SUBJECT = "Payment Transaction was Approved";
    protected $SUCCESSFULEMAIL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
							<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
							<head><style>td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: black;} </style>
							</head>
							<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
								<tr><td>[:LOGO:]</td>
									<td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
								</tr>
								<tr><td colspan=\"2\"><br />For customer service please [:TICKETLINK:].<br></td></tr>
								<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Result</b></td></tr>
								<tr><td><b>Date:</b></td><td>[:TRANS_DATE:]</td></tr>
								<tr><td><b>Reference #:</b></td><td>[:REFNUM:]</td></tr>
								<tr><td><b>Authorization:</b></td><td>[:AUTHNUM:]</td></tr>
								<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Details</b></td></tr>
								<tr><td><b>Paying in:</b></td><td>[:DBA_NAME:]</td></tr>
								<tr><td><b>Type:</b></td><td>Sale</td></tr>
								<tr><td><b>Source:</b></td><td>[:SOURCE:]</td></tr>
								<tr><td><b>Account #:</b></td><td>[:ACCOUNT_NUMBER:] / [:FIRSTNAME:] [:LASTNAME:]</td></tr>
								<tr><td>[:DESCRIPTION:]</td></tr>
								<tr><td colspan=\"2\">[:DISCLAIMER:]</td></tr>
								<tr><td colspan=\"2\"><p style=\"color: #666666; font-size: 12px\"> Please do not reply to this email message, as this email was sent from a notification-only address.</p><p>Revo Payments Inc.</p></td></tr>
							</table>
							</body>
							</html>";
    protected $UNSUCCESSFULEMAIL_SUBJECT = "Payment Transaction was Declined or had errors";
    protected $UNSUCCESSFULEMAIL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
							<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
							<head><style>td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: black;} </style>
							</head>
							<body>[:ERRORMSG:]<table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
								<tr><td>[:LOGO:]</td>
									<td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
								</tr>
								<tr><td colspan=\"2\"><br />For customer service please [:TICKETLINK:].<br></td></tr>
								<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Result</b></td></tr>
								<tr><td><b>Date:</b></td><td>[:TRANS_DATE:]</td></tr>
								<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Details</b></td></tr>
								<tr><td><b>Paying in:</b></td><td>[:DBA_NAME:]</td></tr>
								<tr><td><b>Type:</b></td><td>Sale</td></tr>
								<tr><td><b>Source:</b></td><td>[:SOURCE:]</td></tr>
								<tr><td><b>Account #:</b></td><td>[:ACCOUNT_NUMBER:] / [:FIRSTNAME:]&nbsp;[:LASTNAME:]</td></tr>
								<tr><td>[:DESCRIPTION:]</td></tr>
								<tr><td colspan=\"2\">[:DISCLAIMER:]</td></tr>
								<tr><td colspan=\"2\"><p style=\"color: #666666; font-size: 12px\"> If your payment was an autopay it has been canceled, please create a new autopay.</p></td></tr>
								<tr><td colspan=\"2\"><p style=\"color: #666666; font-size: 12px\"> Please do not reply to this email message, as this email was sent from a notification-only address.</p><p>Revo Payments Inc.</p></td></tr>
							</table>
							</body>
							</html>";
    protected $RECCURRINGENDEMAIL_SUBJECT = "Notification: AutoPay Completed";
    protected $RECCURRINGENDEMAIL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
								<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
								<head>
								<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
								</head>
								<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
									<tr>
										<td>[:LOGO:]</td>
										<td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
									</tr>
                                    <tr><td colspan=\"2\"><br>Hello [:FIRSTNAME:]&nbsp;[:LASTNAME:],<br><br>
										This email is to inform you that the last recurring payment for [:DBA_NAME:]
										has occurred on [:TRANS_DATE:].<br> In order to assure that your payments
										continue uninterrupted and that you do not incur late fees, please log in to [:LOGINLINK:]
										and set up new recurring payments.
									</td></tr>
									<tr><td colspan=\"2\"><br />If you need assistance with logging in to your account, resetting your password or setting up new recurring payments, please [:TICKETLINK:]<br /></td></tr>
									<tr><td colspan=\"2\"><br/>Thank You,<br>[:CONTACTNAME:]<br>[:COMPANYNAME:]<br/></td></tr>
								</table></body></html>";
    protected $RECURRINGEXPIRESEMAIL_SUBJECT = "Notification: AutoPay close to expire";
    protected $RECURRINGEXPIRESEMAIL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
								<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
								<head>
								<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
								</head>
								<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
									<tr>
										<td>[:LOGO:]</td>
										<td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
									</tr>
									<tr><td colspan=\"2\"><br />Hello [:FIRSTNAME:]&nbsp;[:LASTNAME:],<br><br>
									This email is to inform you that you have only one payment left to
									complete your recurring payment cycle for [:DBA_NAME:]. The last
									payment that will complete the recurring payment cycle, is set up for
									[:NEXTDATE:].<br> In order to assure that your
									payments continue uninterrupted and that you do not incur late fees,
									please log in to [:LOGINLINK:] and set up a new recurring
									payment.
								</td></tr>
									<tr><td colspan=\"2\"><br />If you need assistance with logging in to your account, resetting your password or setting up new recurring payments, please [:TICKETLINK:]<br /></td></tr>
									<tr><td colspan=\"2\"><br/>Thank You,<br>[:CONTACTNAME:]<br>[:COMPANYNAME:]<br/></td></tr>
							</table></body></html>";
    protected $INSFEMAIL_SUBJECT = "Payment Transaction Returned";
    protected $INSFEMAIL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
									<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
									<head><style>td {font-family: arial,helvetica,sans-serif;font-size: 10pt;color: #000;}</style></head>
									<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
									<tr><td>[:LOGO:]</td>
										<td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td></tr>
									<tr><td colspan=\"2\"><br />For customer service please [:TICKETLINK:].<br /></td></tr>
									<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Result</b></td></tr>
									<tr><td><b>Date:</b></td><td>[:TRANS_DATE:]</td></tr>
									<tr><td><b>Reference #:</b></td><td>[:REFNUM:]</td></tr>
									<tr><td><b>Authorization:</b></td><td>[:AUTHNUM:]</td></tr>
									<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Details</b></td></tr>
									<tr><td><b>Property Name: </b></td><td>[:DBA_NAME:]</td></tr>
									<tr><td><b>Type:</b></td><td>Sale</td></tr>
									<tr><td><b>Source:</b></td><td>Returned Transaction: [:ERRORMSG:]  </td></tr>
									<tr><td><b>Net Payment:</b></td><td>[:NETAMOUNT:]</td></tr>
									<tr><td valign=\"top\"><b>Payment Details:</b></td><td><pre>[:DESCRIPTION:]</pre></td></tr>
									<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Billing Information</td></tr>
									<tr><td><b>Customer ID:</b></td><td>[:ACCOUNT_NUMBER:]</td></tr>
									<tr><td><b>First Name:</b></td><td>[:FIRSTNAME:]</td></tr>
									<tr><td><b>Last Name:</b></td><td>[:LASTNAME:]</td></tr>
									<tr><td><b>Street:</b></td><td>[:UADDR:]</td></tr>
									<tr><td><b>City:</b></td><td>[:UCITY:]</td></tr>
									<tr><td><b>State:</b></td><td>[:USTATE:]</td></tr>
									<tr><td><b>Zip:</b></td><td>[:UZIP:]</td></tr>
									<tr><td><b>Phone:</b></td><td>[:UPHONE:]</td></tr>
									<tr><td><b>Email:</b></td><td>[:UEMAIL:]</td></tr>
									<tr><td colspan=\"2\">[:DISCLAIMER:]</td></tr>
									</table></body></html>";
    protected $HELPDISCLAIMER = "Please use this form to contact customer service. Our Hours of Operation are from 10am to 7pm CST Monday to Friday. Please contact us regarding issues with the online payments only. All inquiries regarding paper check payments must be addressed with the organization directly. Thank you!";
    protected $NEWHELP = "<label><b>Need Help?</b></label><p>For Amount Due, Account # or Billing Questions, please contact  the company you are paying directly.<br><br>For Technical Support in making your payment, please contact RevoPay at:<br>[:Mcustomerservice@revopay.comM:]<br>[:T 310-593-4833 T:]<br>Monday through Friday: 11am - 8pm ET</p>";
    //protected $userguide = "http://customerservice.revopayments.com/";
    protected $userguide = "https://revopay.freshdesk.com/support/solutions/36000115481/";
    protected $firsttime = "<h5>Activate your account in order to:</h5><ul><li>Make payments</li><li> Setup AutoPay</li><li> Check payment history</li></ul>";
    protected $VOIDSUCCESSFULEMAIL_SUBJECT="Payment Transaction was Voided";
    protected $VOIDSUCCESSFULEMAIL="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
				<head>
				<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
				</head>
				<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
					<tr><td>[:LOGO:]</td><td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
					</tr>
					<tr><td colspan=\"2\"><br />For customer service please For customer service please [:TICKETLINK:].<br /></td></tr>
					<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Result</b></td></tr>
					<tr><td><b>Date:</b></td><td>[:TRANS_DATE:]</td></tr>
					<tr><td><b>Reference #:</b></td><td>[:REFNUM:]</td></tr>
					<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Details</b></td></tr>
					<tr><td><b>Paid in:</b></td><td>[:DBA_NAME:]</td></tr>
					<tr><td><b>Type:</b></td><td>Void</td></tr>
					<tr><td><b>Source:</b></td><td>WEB</td></tr>
					<tr><td><b>Account #:</b></td><td>[:ACCOUNT_NUMBER:] / [:FIRSTNAME:] [:LASTNAME:]</td></tr>
					<tr><td valign=\"top\"><b>Payment Details:</b></td><td>[:DESCRIPTION:]</td></tr></table>
					</body>	</html>";
    protected $REFUNDSUCCESSFULEMAIL_SUBJECT="Payment Transaction was Refunded";
    protected $REFUNDSUCCESSFULEMAIL="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
				<head>
				<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
				</head>
				<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
					<tr><td>[:LOGO:]</td><td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
					</tr>
					<tr><td colspan=\"2\"><br />For customer service please For customer service please [:TICKETLINK:].<br /></td></tr>
					<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Result</b></td></tr>
					<tr><td><b>Date:</b></td><td>[:TRANS_DATE:]</td></tr>
					<tr><td><b>Reference #:</b></td><td>[:REFNUM:]</td></tr>
					<tr><td bgcolor=\"#C4C7D4\" colspan=\"2\"><b>Transaction Details</b></td></tr>
					<tr><td><b>Paid in:</b></td><td>[:DBA_NAME:]</td></tr>
					<tr><td><b>Type:</b></td><td>Void</td></tr>
					<tr><td><b>Source:</b></td><td>WEB</td></tr>
					<tr><td><b>Account #:</b></td><td>[:ACCOUNT_NUMBER:] / [:FIRSTNAME:] [:LASTNAME:]</td></tr>
					<tr><td valign=\"top\"><b>Payment Details:</b></td><td>[:DESCRIPTION:]</td></tr></table>
					</body>	</html>";
    protected $EXPCARD_SUBJECT="Payment Card Expired";
    protected $EXPCARDEMAIL="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
				<head>
				<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
				</head>
				<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
					<tr><td>[:LOGO:]</td><td><h3>Thank you for using Revo Payments, your online Payment Provider.</h3></td>
					</tr>
                                        <tr><td colspan=\"2\">Hello [:FIRSTNAME:] [:LASTNAME:]</td></tr>
					<tr><td colspan=\"2\">Just a friendly reminder that your Card [:CARDTYPE:] has expired, as a result your next scheduled autopay will not process. In order to ensure that your payments continue and that you do not incur late fees please log in to [:LOGINLINK:] and  update the credit card information.</td></tr>
					<tr><td colspan=\"2\"><br />If you need assistance with logging in to your account, resetting your password or setting up new recurring payments, please [:TICKETLINK:].<br /></td></tr>
					</table>
                                        Thank You,<br>[:CONTACTNAME:]<br>[:COMPANYNAME:]
					</body>	</html>";
    protected $TOEXP_SUBJECT="Payment Card close to Expire";
    protected $TOEXPEMAIL="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
				<head>
				<style> td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: #000;} </style>
				</head>
				<body><table border=\"0\" cellpadding=\"1\" cellspacing=\"2\" width=\"90%\">
					<tr><td>[:LOGO:]</td><td></td>
					</tr>
                                        <tr><td colspan=\"2\">Dear [:FIRSTNAME:] [:LASTNAME:]</td></tr>
					<tr><td colspan=\"2\">Just a friendly reminder that your Card [:CARDTYPE:] is due to expire within next 15 days.<br>n order to ensure that your payments continue uninterrupted and that you do not incur late fees, please log into your account and update the Credit Card information.</td></tr>
					<tr><td colspan=\"2\"><br />If you need assistance with logging in to your account, resetting your password or setting up new recurring payments, please [:TICKETLINK:].<br /></td></tr>
					</table>
                                        Thank You,<br>[:CONTACTNAME:]<br>[:COMPANYNAME:]
					</body>	</html>";
    
    // REVO 2329
    // When they forgot the password
    protected $PASSRESETEMAIL_SUBJECT="Reset your password";
    protected $PASSRESETEMAIL= "<!DOCTYPE html >
    <html>
    <head>
        <style>
            td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: black;} 
        </style>
    </head>
    <body>

            <p><b>Dear [:NAME:] [:EMAIL:],</b></p>
            <p>Please [:LOGINLINK:] to complete your password reset.</p>
            <p>
            <p>If you need further assistance, please [:CONTACTUS:] during regular business hours.</p>
            <p>Thank you!</p>

            <p style=\"color: #666666; font-size: 12px\"> Please do not reply to this email message, as this email was sent from a notification-only address.</p>     

    </body>
    </html>";

    // 2329: When the admin changes the password.
    protected $PASSCHANGEEMAIL_SUBJECT="Your Password Changed";
    protected $PASSCHANGEEMAIL="<!DOCTYPE html >
    <html>
    <head>
        <style>
            td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: black;} 
        </style>
    </head>
    <body>

        <p>[:USERNAME:] your password is changed.</p>

        <p style=\"color: #666666; font-size: 12px\"> Please do not reply to this email message, as this email was sent from a notification-only address.</p>     

    </body>
    </html>";

    function getSettingsValuesPartner($id_groups, $published = 1) {
        $partIdKey = array();

        if ($id_groups > 0) {
            if ($published == 'all') {
                $results = DB::table('settings_values')->where('id_groups', '=', $id_groups)->select('id', 'value', 'key')->get();
            } else {
                $results = DB::table('settings_values')->where('id_groups', '=', $id_groups)->where('published', '=', $published)->select('id', 'value', 'key')->get();
            }

            foreach ($results as $idkey) {
                $partIdKey[strtoupper($idkey->key)] = $idkey->value;
            }
        }
        return $partIdKey;
    }

    function getSettingsValuesGroup($id_group, $id_partner, $published = 1) {
        $id_groups = $this->getPartnersGroup($id_partner);
        $partnervalue = $this->getSettingsValuesPartner($id_groups);
        if ($id_group > 0) {
            $groupIdKey = array();
            if ($published == 'all') {
                $results = DB::table('settings_values')->where('id_groups', '=', $id_group)->select('id', 'value', 'key')->get();
            } else {
                $results = DB::table('settings_values')->where('id_groups', '=', $id_group)->where('published', '=', $published)->select('id', 'value', 'key')->get();
            }
            //set parnter values to group
            foreach ($results as $idkey) {
                $groupIdKey[strtoupper($idkey->key)] = $idkey->value;
            }
            foreach ($groupIdKey as $key => $value) {
                $partnervalue[$key] = $value;
            }
        }
        return $partnervalue;
    }

    function getSettingValueGroup($id_group, $id_partner, $key) {
        $id_groups = $this->getPartnersGroup($id_partner);
        $partnervalue = $this->getSettingsValue($id_groups, $key);
        if ($id_group > 0) {
            $groupvalue = $this->getSettingsValue($id_group, $key);
            if ($groupvalue == null) {
                return $partnervalue;
            }
            return $groupvalue;
        }
        return $partnervalue;
    }

    function getSettingValueProperty($id_partner, $id_company, $id_prop_group, $key) {
        $id_group = $this->getCompaniesGroup($id_company);
        $groupvalues = $this->getSettingValueGroup($id_group, $id_partner, $key);
        if ($id_prop_group > 0) {
            $groupvalue = $this->getSettingsValue($id_prop_group, $key);
            if ($groupvalue == null) {
                return $groupvalues;
            }
            return $groupvalue;
        }
        return $groupvalues;
    }

    function getSettingsValueProperties($id_partner, $id_company, $id_prop_group, $published = 1) {

        $id_group = $this->getCompaniesGroup($id_company);

        $groupvalues = $this->getSettingsValuesGroup($id_group, $id_partner);
        if ($id_prop_group > 0) {
            if ($published == 'all') {
                $results = DB::table('settings_values')->where('id_groups', '=', $id_prop_group)->select('id', 'value', 'key')->get();
            } else {
                $results = DB::table('settings_values')->where('id_groups', '=', $id_prop_group)->where('published', '=', $published)->select('id', 'value', 'key')->get();
            }

            $propIdKey = array();
            foreach ($results as $idkey) {
                $propIdKey[strtoupper($idkey->key)] = $idkey->value;
            }
            foreach ($propIdKey as $key => $value) {
                $groupvalues[$key] = $value;
            }
        }
        return $groupvalues;
    }

    function getPartnersGroup($idlevel) {
        $id_groups = DB::table('partners_settings_groups')->where('id_partners', '=', $idlevel)->select('id_settings_groups')->first();
        if (empty($id_groups)) {
            return 0;
        }
        return $id_groups->id_settings_groups;
    }

    function getPropertiesGroup($idLevel) {
        $id_groups = DB::table('properties_settings_groups')
                        ->where('id_properties', '=', $idLevel)
                        ->select('id_settings_groups')->first();
        if (empty($id_groups)) {
            return 0;
        }
        return $id_groups->id_settings_groups;
    }

    function getCompaniesGroup($idLevel) {
        $idgroups = DB::table('companies_settings_groups')->where('id_companies', '=', $idLevel)->select('id_settings_groups')->first();
        if (empty($idgroups)) {
            return 0;
        }
        return $idgroups->id_settings_groups;
    }

    function createPartnerGroup($idlevel) {
        $idgroups = DB::table('settings_groups')->insertGetId(array('type' => 'settings', 'name' => 'partner:' . $idlevel));
        DB::table('partners_settings_groups')->insert(array('id_partners' => $idlevel, 'id_settings_groups' => $idgroups));
        return $idgroups;
    }

    function createCompanyGroup($idlevel) {
        $idgroups = DB::table('settings_groups')->insertGetId(array('type' => 'settings', 'name' => 'company:' . $idlevel));
        DB::table('companies_settings_groups')->insert(array('id_companies' => $idlevel, 'id_settings_groups' => $idgroups));
        return $idgroups;
    }

    function createPropertyGroup($idlevel) {
        $idgroups = DB::table('settings_groups')->insertGetId(array('type' => 'settings', 'name' => 'property:' . $idlevel));
        DB::table('properties_settings_groups')->insert(array('id_properties' => $idlevel, 'id_settings_groups' => $idgroups));
        return $idgroups;
    }

    function saveSettingValue($idgroup, $key, $value, $level, $idlevel, $pub = 1) {
        if ($idgroup <= 0) {
            return;
        }
        $result = DB::table('settings_values')->where('id_groups', '=', $idgroup)->where('key', 'like', $key)->select('id')->first();

        if (empty($result)) {
            //insert
            DB::table('settings_values')->insert(array('id_groups' => $idgroup, 'key' => strtoupper($key), 'value' => trim($value), 'published' => $pub));
            $this->updateCache($level, $idlevel, $key, $value, 'I');
        } else {
            //update
            DB::table('settings_values')->where('id', '=', $result->id)->where('published', '=', $pub)->update(array('key' => strtoupper($key), 'value' => trim($value)));
            $this->updateCache($level, $idlevel, $key, $value, 'U');
        }
    }

    function getUpdateSettings($idgroup, $key){
        if ($idgroup <= 0) {
            return;
        }
        $result = DB::table('settings_values')->where('id_groups', '=', $idgroup)->where('key', 'like', $key)->select('value')->first();
        return json_decode(json_encode($result), true);
    }
    
    function updateCache($level,$idlevel,$key,$value,$op='U'){
        $settingkey='';
        if($level=='P'){
            $settingkey= str_pad($idlevel,6,'0',STR_PAD_LEFT).'000000'.'000000000000';
        }
        elseif($level=='G'){
            $r= \Illuminate\Support\Facades\DB::table('companies')->where('id',$idlevel)->select('id_partners')->first();
            $settingkey= str_pad($r->id_partners,6,'0',STR_PAD_LEFT).str_pad($idlevel,6,'0',STR_PAD_LEFT).'000000000000';
            
        }elseif($level=='M'){
            $r= \Illuminate\Support\Facades\DB::table('properties')->where('id',$idlevel)->select('id_partners','id_companies')->first();
            $settingkey= str_pad($r->id_partners,6,'0',STR_PAD_LEFT).str_pad($r->id_companies,6,'0',STR_PAD_LEFT).str_pad($idlevel,12,'0',STR_PAD_LEFT);
        }
        if($settingkey=='')return;
        if($op=='U'){
            \Illuminate\Support\Facades\DB::table('settings_cache_values')->where('settings_key',$settingkey)->where('key', strtoupper($key))->update(['value'=>$value]);
        }
        else {
            $r=\Illuminate\Support\Facades\DB::table('settings_cache_values')->where('settings_key',$settingkey)->where('key', strtoupper($key))->first();
            if(empty($r)){
                \Illuminate\Support\Facades\DB::table('settings_cache_values')->insert(['value'=>$value,'key'=>$key,'settings_key'=>$settingkey]);
            }
            else {
                \Illuminate\Support\Facades\DB::table('settings_cache_values')->where('settings_key',$settingkey)->where('key', strtoupper($key))->update(['value'=>$value]);
            }
        }
    }

    function saveSettingValueAux($idgroup, $key, $value) {
        if ($idgroup <= 0) {
            return;
        }
        $result = DB::table('settings_values')->where('id_groups', '=', $idgroup)->where('key', '=', $key)->select('id')->first();
        if (empty($result)) {
            //insert
            DB::table('settings_values')->insert(array('id_groups' => $idgroup, 'key' => strtoupper($key), 'value' => trim($value)));
        } else {
            //update
            DB::table('settings_values')->where('id', '=', $result['id'])->update(array('key' => strtoupper($key), 'value' => trim($value)));
        }
    }

    function setValueKeyByGroup($idgroup, $key, $value) {
        DB::table('settings_values')->where('id_groups', '=', $idgroup)->update(array($key => $value));
    }

    function refreshSettingValue($idgroup, $key, $value, $pub = 1) {
        if ($idgroup <= 0) {
            return;
        }
        $result = DB::table('settings_values')->where('id_groups', '=', $idgroup)->where('key', 'like', $key)->select('id')->first();
        if (empty($result)) {
            //insert
            DB::table('settings_values')->insert(array('id_groups' => $idgroup, 'key' => strtoupper($key), 'value' => trim($value), 'published' => $pub));
        }
    }

    function removeSettingUnderPartner($idlevel, $key) {
        $result = DB::table('companies')->where('id_partners', '=', $idlevel)->select('id')->get();
        foreach ($result as $company) {
            $this->removeSettingUnderCompany($company->id, $key);
            $this->removeSettingCompany($company->id, $key);
        }
    }

    function removeSettingUnderCompany($idlevel, $key) {
        $result = DB::table('properties')->where('id_companies', '=', $idlevel)->select('id')->get();
        foreach ($result as $property) {
            $this->removeSettingMerchant($property->id, $key);
        }
    }

    function removeSettingMerchant($idlevel, $key) {
        $idgroup = $this->getPropertiesGroup($idlevel);
        if (!empty($idgroup)) {
            $result = DB::table('settings_values')->where('id_groups', '=', $idgroup)->where('key', 'like', $key)->select('id')->first();
            if (!empty($result)) {
                $this->where('id', '=', $result->id)->delete();
            }
        }
    }

    function removeSettingCompany($idlevel, $key) {
        $idgroup = $this->getCompaniesGroup($idlevel);
        if (!empty($idgroup)) {
            $result = DB::table('settings_values')->where('id_groups', '=', $idgroup)->where('key', 'like', $key)->select('id')->first();
            if (!empty($result)) {
                $this->where('id', '=', $result->id)->delete();
            }
        }
    }

    function UpdatePublishedUnderPartner($idgroup, $idlevel) {
        $result = $this->where('id_groups', '=', $idgroup)->where('published', '=', 0)->select('key')->get();
        foreach ($result as $setting) {
            $this->UnpublishSettingUnderPartner($idlevel, $setting['key']);
        }
    }

    function UpdatePublishedUnderCompany($idlevel) {
        $result = DB::table('companies')->where('id', '=', $idlevel)->select('id_partners')->first();
        $idpartner = $result->id_partners;
        $idgroup = $this->getPartnersGroup($idpartner);
        $result = $this->where('id_groups', '=', $idgroup)->where('published', '=', 0)->select('key', 'value')->get();
        foreach ($result as $setting) {
            $this->UnpublishSettingUnderCompany($idlevel, $setting['key']);
            $this->UnpublishSettingCompany($idlevel, $setting['key'], $setting['value']);
        }
    }

    function UpdatePublishedUnderMerchant($idlevel) {
        $result = DB::table('properties')->where('id', '=', $idlevel)->select('id_partners')->first();
        $idpartner = $result->id_partners;
        $idgroup = $this->getPartnersGroup($idpartner);
        $result = $this->where('id_groups', '=', $idgroup)->where('published', '=', 0)->select('key', 'value')->get();
        foreach ($result as $setting) {
            $this->UnpublishSettingMerchant($idlevel, $setting['key'], $setting['value']);
        }
    }

    function UnpublishSettingUnderPartner($idlevel, $key) {
        $result = DB::table('companies')->where('id_partners', '=', $idlevel)->select('id')->get();
        $idgroup = $this->getPartnersGroup($idlevel);
        $result1 = DB::table($this->table)->where('id_groups', '=', $idgroup)->where('key', 'LIKE', $key)->select('value')->first();
        $vv = '';
        if (!empty($result1)) {
            $vv = $result1->value;
        }
        foreach ($result as $company) {
            $this->UnpublishSettingUnderCompany($company->id, $key);
            $this->UnpublishSettingCompany($company->id, $key);
        }
    }

    function UnpublishSettingUnderCompany($idlevel, $key) {
        $result = DB::table('properties')->where('id_companies', '=', $idlevel)->select('id')->get();
        foreach ($result as $property) {
            $this->UnpublishSettingMerchant($property->id, $key);
        }
    }

    function UnpublishSettingMerchant($idlevel, $key, $value = '') {
        $idgroup = $this->getPropertiesGroup($idlevel);
        if (!empty($idgroup)) {
            $this->where('id_groups', '=', $idgroup)->where('key', 'LIKE', $key)->update(array('published' => 0, 'value' => $value));
        }
    }

    function UnpublishSettingCompany($idlevel, $key, $value = '') {
        $idgroup = $this->getCompaniesGroup($idlevel);
        if (!empty($idgroup)) {
            $this->where('id_groups', '=', $idgroup)->where('key', 'LIKE', $key)->update(array('published' => 0, 'value' => $value));
        }
    }

    function getDefaultInvoiceCancel(){
        return $this->INVOICECANCELMAIL;
    }

    function getDefaultInvoiceSubject(){
        return $this->INVOICECANCEL_SUBJECT;
    }

    function getDefaultReminderSubject(){
        return $this->AUTOREMINDEREMAIL_SUBJECT_TEMPLATE;
    }

    function getDefaultReminder(){
        return $this->AUTOREMINDEREMAILTEMPLATE;
    }
    
    function getDefaultVoidSubject(){
        return $this->VOIDSUCCESSFULEMAIL_SUBJECT;
    }
    
    function getDefaultVoidEmail(){
        return $this->VOIDSUCCESSFULEMAIL;
    }

    function getDefaultRefundSubject(){
        return $this->REFUNDSUCCESSFULEMAIL_SUBJECT;
    }
    
    function getDefaultRefundEmail(){
        return $this->REFUNDSUCCESSFULEMAIL;
    }
    
    function getDefaultExpSubject(){
        return $this->EXPCARD_SUBJECT;
    }
    
    function getDefaultExpEmail(){
        return $this->EXPCARDEMAIL;
    }

    function getDefaultAutoSubject(){
        return $this->AUTOSUCCESSFULEMAIL_SUBJECT;
    }
    
    function getDefaultAutoEmail(){
        return $this->AUTOSUCCESSFULEMAIL;
    }
    
    function getDefault2ExpSubject(){
        return $this->TOEXP_SUBJECT;
    }
    
    function getDefault2ExpEmail(){
        return $this->TOEXPEMAIL;
    }

    // REVO 2329
    function getDefaultPassResetSubject(){
        return $this->PASSRESETEMAIL_SUBJECT;
    }
    
    function getDefaultPassResetEmail(){
        return $this->PASSRESETEMAIL;
    }

    function getDefaultChangeResetSubject(){
        return $this->PASSCHANGEEMAIL_SUBJECT;
    }
    
    function getDefaultPassChangeEmail(){
        return $this->PASSCHANGEEMAIL;
    }
    
    function createDefaultPartnerValues($idgroup,$idpartner) {
        if ($idgroup <= 0) {
            return;
        }
        $this->saveSettingValue($idgroup, 'accsetting', 4,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'invsetting', 1,'P',$idpartner);
        //reports
        $this->saveSettingValue($idgroup, 'CM8', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKMRI', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKSKYLINE', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKTOPS', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKSAGE', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKYARDI', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKQBOOKS', '1','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKPEACHTREE', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKJENARK', '0','P',$idpartner);
        $this->saveSettingValue($idgroup, 'CHECKEXCEL', '1','P',$idpartner);
        //email templates
        $this->saveSettingValue($idgroup, 'AUTOSUCCESSFULEMAIL', $this->AUTOSUCCESSFULEMAIL_SUBJECT . '|' . $this->AUTOSUCCESSFULEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'SUCCESSFULEMAIL', $this->SUCCESSFULEMAIL_SUBJECT . '|' . $this->SUCCESSFULEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'UNSUCCESSFULEMAIL', $this->UNSUCCESSFULEMAIL_SUBJECT . '|' . $this->UNSUCCESSFULEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'INSFEMAIL', $this->INSFEMAIL_SUBJECT . '|' . $this->INSFEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'RECCURRINGENDEMAIL', $this->RECCURRINGENDEMAIL_SUBJECT . '|' . $this->RECCURRINGENDEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'RECURRINGEXPIRESEMAIL', $this->RECURRINGEXPIRESEMAIL_SUBJECT . '|' . $this->RECURRINGEXPIRESEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'VOIDSUCCESSFULEMAIL', $this->VOIDSUCCESSFULEMAIL_SUBJECT . '|' . $this->VOIDSUCCESSFULEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'REFUNDSUCCESSFULEMAIL', $this->REFUNDSUCCESSFULEMAIL_SUBJECT . '|' . $this->REFUNDSUCCESSFULEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'EXPCARDEMAIL', $this->EXPCARD_SUBJECT . '|' . $this->EXPCARDEMAIL,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'TOEXPEMAIL', $this->TOEXP_SUBJECT . '|' . $this->TOEXPEMAIL,'P',$idpartner);
        //notifications
        $this->saveSettingValue($idgroup, 'donotreplyemail', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notrecurringendemail', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notrecurringexpiresemail', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notunsuccessfulemail', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notnsf', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notsuccessfulemail', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notsuccessfulemailrec', 0,'P',$idpartner);
        //paymentpage
        $this->saveSettingValue($idgroup, 'LOCKED_PROFILE_FIELDS_SELF', '','P',$idpartner);
        $this->saveSettingValue($idgroup, 'oneclick', 1,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'showlimit1', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'defaultpc', 'Payment','P',$idpartner);
        $this->saveSettingValue($idgroup, 'show_balance', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'SETTLMENT_DISCLAIMER', '','P',$idpartner);
        $this->saveSettingValue($idgroup, 'FIXEDRECURRING', 1,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'DYNAMICRECURRING', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'keepauto', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'MAXRECURRINGPAYMENTPERUSER', 1,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'FREQAUTOPAY', 'monthly|quarterly|biannually|annually|untilcancel','P',$idpartner);
        $this->saveSettingValue($idgroup, 'DAYSAUTOPAY', '1|31','P',$idpartner);
        $this->saveSettingValue($idgroup, 'DYNAMICRECURRINGTEXT', '<b>Enable Dynamic Auto-payment</b> By checking this box I hereby authorize ".$property_name." to charge my payment account for whatever fees I owe on the schedule I establish below. This auto-payment authorization can be cancelled at any time by going to My Scheduled AutoPays and clicking on cancel. Thank you for your payment.','P',$idpartner);
        $this->saveSettingValue($idgroup, 'DRPFREQAUTOPAY', 'monthly|quarterly|biannually|annually|untilcancel','P',$idpartner);
        $this->saveSettingValue($idgroup, 'DRPDAYSAUTOPAY', '1|31','P',$idpartner);
        $this->saveSettingValue($idgroup, 'nomemo', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'nosocial', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'CANCELRESTRICTIONS', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'NOETERMNEWUSER', 0,'P',$idpartner);
        //general
        $this->saveSettingValue($idgroup, 'notreg', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'manager_approval_not_required', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'payment_number_logon_welcome', $this->firsttime,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'payment_number_reg_number', 'Account #','P',$idpartner);
        $this->saveSettingValue($idgroup, 'payment_number_reg_welcome', 'Please enter your account # to continue:','P',$idpartner);
        $this->saveSettingValue($idgroup, 'payment_numbers', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'acchide', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'nonewuser', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notaccqp', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'accsetting', 4,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'invsetting', 1,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'notinvreq', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'INVOICETAXES', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'days_after_due_value', '','P',$idpartner);
        $this->saveSettingValue($idgroup, 'freq_after_due', '','P',$idpartner);
        $this->saveSettingValue($idgroup, 'freq_before_due', '','P',$idpartner);
        $this->saveSettingValue($idgroup, 'SEND_REMINDERS', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'INVOICEDEFAULTQUICKPAY', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'EINVOICE', 1,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'paynow', 1,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'regreg', 0, 0,'P',$idpartner);
        //layout
        $this->saveSettingValue($idgroup, 'hidemerchantinfo', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'REPLYTCKCUST', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'default_from', 'customerservice@revopayments.com','P',$idpartner);
        $this->saveSettingValue($idgroup, 'helpdisclaimer', $this->HELPDISCLAIMER,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'newhelp', $this->NEWHELP,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'userguide', $this->userguide,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'maintenance', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'maintenancemail', '','P',$idpartner);
        $this->saveSettingValue($idgroup, 'qplogin', 0,'P',$idpartner);
        $this->saveSettingValue($idgroup, 'showcompanyname', 0,'P',$idpartner);
        //reminder
        $this->saveSettingValue($idgroup, 'AUTOREMINDER', 0,'P',$idpartner);
    }

    function refreshPartnerSettings($idgroup) {
        if ($idgroup <= 0) {
            return;
        }
        $this->refreshSettingValue($idgroup, 'accsetting', 4);
        $this->refreshSettingValue($idgroup, 'invsetting', 1);
        //reports
        $this->refreshSettingValue($idgroup, 'CM8', '0');
        $this->refreshSettingValue($idgroup, 'CHECKMRI', '0');
        $this->refreshSettingValue($idgroup, 'CHECKSKYLINE', '0');
        $this->refreshSettingValue($idgroup, 'CHECKTOPS', '0');
        $this->refreshSettingValue($idgroup, 'CHECKSAGE', '0');
        $this->refreshSettingValue($idgroup, 'CHECKYARDI', '0');
        $this->refreshSettingValue($idgroup, 'CHECKQBOOKS', '1');
        $this->refreshSettingValue($idgroup, 'CHECKPEACHTREE', '0');
        $this->refreshSettingValue($idgroup, 'CHECKJENARK', '0');
        $this->refreshSettingValue($idgroup, 'CHECKEXCEL', '1');
        //email templates
        $this->refreshSettingValue($idgroup, 'SUCCESSFULEMAIL', $this->SUCCESSFULEMAIL_SUBJECT . '|' . $this->SUCCESSFULEMAIL);
        $this->refreshSettingValue($idgroup, 'UNSUCCESSFULEMAIL', $this->UNSUCCESSFULEMAIL_SUBJECT . '|' . $this->UNSUCCESSFULEMAIL);
        $this->refreshSettingValue($idgroup, 'INSFEMAIL', $this->INSFEMAIL_SUBJECT . '|' . $this->INSFEMAIL);
        $this->refreshSettingValue($idgroup, 'RECCURRINGENDEMAIL', $this->RECCURRINGENDEMAIL_SUBJECT . '|' . $this->RECCURRINGENDEMAIL);
        $this->refreshSettingValue($idgroup, 'RECURRINGEXPIRESEMAIL', $this->RECURRINGEXPIRESEMAIL_SUBJECT . '|' . $this->RECURRINGEXPIRESEMAIL);
        //notifications
        $this->refreshSettingValue($idgroup, 'donotreplyemail', 0);
        $this->refreshSettingValue($idgroup, 'notrecurringendemail', 0);
        $this->refreshSettingValue($idgroup, 'notrecurringexpiresemail', 0);
        $this->refreshSettingValue($idgroup, 'notunsuccessfulemail', 0);
        $this->refreshSettingValue($idgroup, 'notnsf', 0);
        $this->refreshSettingValue($idgroup, 'notsuccessfulemail', 0);
        $this->refreshSettingValue($idgroup, 'notsuccessfulemailrec', 0);
        //paymentpage
        $this->refreshSettingValue($idgroup, 'LOCKED_PROFILE_FIELDS_SELF', '');
        $this->refreshSettingValue($idgroup, 'oneclick', 1);
        $this->refreshSettingValue($idgroup, 'showlimit1', 0);
        $this->refreshSettingValue($idgroup, 'defaultpc', 'Payment');
        $this->refreshSettingValue($idgroup, 'show_balance', 0);
        $this->refreshSettingValue($idgroup, 'SETTLMENT_DISCLAIMER', '');
        $this->refreshSettingValue($idgroup, 'FIXEDRECURRING', 1);
        $this->refreshSettingValue($idgroup, 'DYNAMICRECURRING', 0);
        $this->refreshSettingValue($idgroup, 'keepauto', 0);
        $this->refreshSettingValue($idgroup, 'MAXRECURRINGPAYMENTPERUSER', 1);
        $this->refreshSettingValue($idgroup, 'FREQAUTOPAY', 'monthly|quarterly|biannually|annually|untilcancel');
        $this->refreshSettingValue($idgroup, 'DAYSAUTOPAY', '1|31');
        $this->refreshSettingValue($idgroup, 'DYNAMICRECURRINGTEXT', '<b>Enable Dynamic Auto-payment</b> By checking this box I hereby authorize ".$property_name." to charge my payment account for whatever fees I owe on the schedule I establish below. This auto-payment authorization can be cancelled at any time by going to My Scheduled AutoPays and clicking on cancel. Thank you for your payment.');
        $this->refreshSettingValue($idgroup, 'DRPFREQAUTOPAY', 'monthly|quarterly|biannually|annually|untilcancel');
        $this->refreshSettingValue($idgroup, 'DRPDAYSAUTOPAY', '1|31');
        $this->refreshSettingValue($idgroup, 'nomemo', 0);
        $this->refreshSettingValue($idgroup, 'nosocial', 0);
        $this->refreshSettingValue($idgroup, 'CANCELRESTRICTIONS', 0);
        $this->refreshSettingValue($idgroup, 'NOETERMNEWUSER', 0);
        //general
        $this->refreshSettingValue($idgroup, 'notreg', 0);
        $this->refreshSettingValue($idgroup, 'manager_approval_not_required', 0);
        $this->refreshSettingValue($idgroup, 'payment_number_logon_welcome', $this->firsttime);
        $this->refreshSettingValue($idgroup, 'payment_number_reg_number', 'Account #');
        $this->refreshSettingValue($idgroup, 'payment_number_reg_welcome', 'Please enter your account # to continue:');
        $this->refreshSettingValue($idgroup, 'payment_numbers', 0);
        $this->refreshSettingValue($idgroup, 'acchide', 0);
        $this->refreshSettingValue($idgroup, 'nonewuser', 0);
        $this->refreshSettingValue($idgroup, 'notaccqp', 0);
        $this->refreshSettingValue($idgroup, 'accsetting', 4);
        $this->refreshSettingValue($idgroup, 'invsetting', 1);
        $this->refreshSettingValue($idgroup, 'notinvreq', 0);
        $this->refreshSettingValue($idgroup, 'INVOICETAXES', 0);
        $this->refreshSettingValue($idgroup, 'days_after_due_value', '');
        $this->refreshSettingValue($idgroup, 'freq_after_due', '');
        $this->refreshSettingValue($idgroup, 'freq_before_due', '');
        $this->refreshSettingValue($idgroup, 'SEND_REMINDERS', 0);
        $this->refreshSettingValue($idgroup, 'INVOICEDEFAULTQUICKPAY', 0);
        $this->refreshSettingValue($idgroup, 'EINVOICE', 1);
        $this->refreshSettingValue($idgroup, 'paynow', 1);
        $this->refreshSettingValue($idgroup, 'regreg', 0, 0);
        //layout
        $this->refreshSettingValue($idgroup, 'hidemerchantinfo', 0);
        $this->refreshSettingValue($idgroup, 'REPLYTCKCUST', 0);
        $this->refreshSettingValue($idgroup, 'default_from', 'customerservice@revopayments.com');
        $this->refreshSettingValue($idgroup, 'helpdisclaimer', $this->HELPDISCLAIMER);
        $this->refreshSettingValue($idgroup, 'newhelp', $this->NEWHELP);
        $this->refreshSettingValue($idgroup, 'userguide', $this->userguide);
        $this->refreshSettingValue($idgroup, 'maintenance', 0);
        $this->refreshSettingValue($idgroup, 'maintenancemail', '');
        $this->refreshSettingValue($idgroup, 'qplogin', 0);
        $this->refreshSettingValue($idgroup, 'showcompanyname', 0);
        //reminder
        $this->refreshSettingValue($idgroup, 'AUTOREMINDER', 0);
    }

    function getSettingsValue($id_group, $key) {
        if ($id_group <= 0) {
            return null;
        }
        $results = $this->where('id_groups', '=', $id_group)->where('key', 'like', $key)->select('value')->first();
        if (empty($results)) {
            return null;
        }
        return $results['value'];
    }

    function getPartnerswithSetting($key) {
        $result = DB::table('partners_settings_groups')
                ->join('settings_values', 'settings_values.id_groups', '=', 'partners_settings_groups.id_settings_groups')
                ->join('partners', 'partners_settings_groups.id_partners', '=', 'partners.id')
                ->where('settings_values.key', 'LIKE', $key)
                ->where('settings_values.value', '!=', '')
                ->select('partners_settings_groups.id_partners as idp', 'settings_values.value', 'settings_values.id_groups', 'partners.partner_title as name', 'partners.partner_name as CID')
                ->groupBy('partners_settings_groups.id_partners')
                ->get();
        return $result;
    }

    function getGroupswithSetting($key) {
        $result = DB::table('companies_settings_groups')
                ->join('settings_values', 'settings_values.id_groups', '=', 'companies_settings_groups.id_settings_groups')
                ->join('companies', 'companies_settings_groups.id_companies', '=', 'companies.id')
                ->where('settings_values.key', 'LIKE', $key)
                ->where('settings_values.value', '!=', '')
                ->select('companies_settings_groups.id_companies as idp', 'settings_values.value', 'settings_values.id_groups', 'companies.company_name as name', 'companies.compositeID_companies as CID')
                ->groupBy('companies_settings_groups.id_companies')
                ->get();
        return $result;
    }

    function getMerchantswithSetting($key) {
        $result = DB::table('properties_settings_groups')
                ->join('settings_values', 'settings_values.id_groups', '=', 'properties_settings_groups.id_settings_groups')
                ->join('properties', 'properties_settings_groups.id_properties', '=', 'properties.id')
                ->where('settings_values.key', 'LIKE', $key)
                ->where('settings_values.value', '!=', '')
                ->select('properties_settings_groups.id_properties as idp', 'settings_values.value', 'settings_values.id_groups', 'properties.name_clients as name', 'properties.compositeID_clients as CID')
                ->groupBy('properties_settings_groups.id_properties')
                ->get();
        return $result;
    }

    function getByGroup($idgroup) {
        $result = DB::table('settings_values')->where('id_groups', '=', $idgroup)->get();
        return $result;
    }

    function setPublished($idgroup, $key, $value) {
        DB::table('settings_values')
                ->where('id_groups', '=', $idgroup)
                ->where('key', '=', $key)
                ->update(array(
                    'published' => $value
        ));
    }

    /**
     * Gets the setting by Level(P,G,M), id and key
     * @param string $level
     * @param int $idLevel
     * @param string $key
     * @return string
     */
    function getSettingByKey($level, $idLevel, $key) {
        $settingValue = '';
        if ($level == 'P') {
            $partnerSettingId = $this->getPartnersGroup($idLevel);
            $settingValue = $this->getSettingsValue($partnerSettingId, $key);
        } elseif ($level == 'G') {
            $groupSettingId = $this->getCompaniesGroup($idLevel);
            $objCompany = new Companies();
            $idPartner = $objCompany->getPartnerID($idLevel);
            $settingValue = $this->getSettingValueGroup($groupSettingId, $idPartner, $key);
        } elseif ($level == 'M') {
            $merchantSettingId = $this->getPropertiesGroup($idLevel);
            $objProperty = new Properties();
            $idPartner = $objProperty->get1PropertyInfo($idLevel, 'id_partners');
            $idCompany = $objProperty->get1PropertyInfo($idLevel, 'id_companies');
            $settingValue = $this->getSettingValueProperty($idPartner, $idCompany, $merchantSettingId, $key);
        }

        return $settingValue;
    }
    
    function removeSettingValue($idgroup, $key) {
        if ($idgroup <= 0) {
            return;
        }
        DB::table('settings_values')->where('id_groups', '=', $idgroup)->where('key', 'like', $key)->delete();
    }
    
}
