<?php
    $message->from('noreply@revopay.com','Revopay Admin')->subject('Your Password Changed');
    $message->to($email_address);
?>    
<!DOCTYPE html >
    <html>
    <head>
        <style>
            td {font-family: arial,helvetica,sans-serif; font-size: 10pt; color: black;} 
        </style>
    </head>
    <body>
        <table border="0" cellpadding="1" cellspacing="2" width="90%">            
            <tr>
                <td colspan="2"><br /> </td>
            </tr>
            <tr>
                <td colspan="2"><br></td>
            </tr>
            <tr>
                <td colspan="2">
                    <p><?php echo $username ?> your password is changed.</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p style="color: #666666; font-size: 12px"> Please do not reply to this email message, as this email was sent from a notification-only address.</p>                    
                </td>
            </tr>
        </table>
    </body>
    </html>