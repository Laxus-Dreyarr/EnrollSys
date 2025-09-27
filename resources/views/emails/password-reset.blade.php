<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Password Reset Code</title>
</head>
<body style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height:1.6; color:#333333; margin:0; padding:0; background-color:#f7f7f7;">

  <!-- Wrapper -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f7f7f7; padding:20px 0;">
    <tr>
      <td align="center">

        <!-- Main Container -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:8px; box-shadow:0 5px 15px rgba(0,0,0,0.1); overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td align="center" style="background:linear-gradient(135deg, #2c3e50 0%, #3498db 100%); padding:25px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="background-color:#ffffff; border-radius:8px; padding:15px; box-shadow:0 3px 10px rgba(0,0,0,0.1);">
                    <!-- CORRECTED: Use Blade syntax for image embedding -->
                    <img src="{{ $message->embed(public_path('logo.png')) }}" alt="EnrollSys Logo" style="height:100px; display:block; margin:0 auto; ">
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:20px;">
              <p style="margin:0 0 15px 0;">Dear user,</p>

              <p style="margin:0 0 20px 0;">You recently requested to reset your password for your EnrollSys account. Use the verification code below to complete the process.</p>

              <!-- Code Section -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0;">
                <tr>
                  <td align="center" style="background-color:#f8f9fa; border-left:4px solid #3498db; padding:20px; border-radius:4px;">
                    <h3 style="margin:0 0 15px 0; color:#2c3e50; font-weight:normal;">Your Password Reset Code</h3>
                    <div style="font-size:28px; font-weight:bold; letter-spacing:5px; color:#2c3e50; padding:12px; margin:15px 0; background:#ffffff; border:2px dashed #3498db; border-radius:8px; display:inline-block; max-width:90%; width:auto; text-align:center;">{{ $otp }}</div>
                  </td>
                </tr>
              </table>

              <!-- Warning Box -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0;">
                <tr>
                  <td style="background-color:#fff3e0; border-left:4px solid #ff9800; padding:15px; border-radius:4px; font-size:14px;">
                    <strong>Security Note:</strong> If you didn't request this password reset, please ignore this email or contact support if you have concerns about your account's security.
                  </td>
                </tr>
              </table>

              <p style="margin:20px 0;">For assistance, please contact the EnrollSys support team at 
                <a href="mailto:support@evsu.ormoc.ph" style="color:#3498db; text-decoration:none;">support@evsu.ormoc.ph</a>.
              </p>

              <!-- Signature -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:25px; border-top:1px solid #eeeeee;">
                <tr>
                  <td style="padding-top:15px;">
                    <p style="margin:0;">Best regards,<br>
                    <strong>Team CyberNexus (EnrollSys)</strong><br>
                    EVSU Ormoc Campus</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="background-color:#f2f2f2; padding:15px; font-size:12px; color:#666666;">
              <p style="margin:0;">© {{ date('Y') }} EnrollSys - EVSU Ormoc Campus. All rights reserved.</p>
              <p style="margin:5px 0;">This is an automated message, please do not reply directly to this email.</p>
              <p style="margin:5px 0;">
                <a href="#" style="color:#3498db; text-decoration:none;">Privacy Policy</a> | 
                <a href="#" style="color:#3498db; text-decoration:none;">Terms of Service</a>
              </p>
            </td>
          </tr>

        </table>
        <!-- End Main Container -->

      </td>
    </tr>
  </table>
  <!-- End Wrapper -->

</body>
</html>