<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Password Reset Code</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="font-family:'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height:1.6; color:#333333; margin:0; padding:0; background-color:#f4f6f8;">

  <!-- Wrapper -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="padding:30px 0;">
    <tr>
      <td align="center">

        <!-- Main Container -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.08); overflow:hidden;">
          
          <!-- Header with Logo and Title -->
          <tr>
            <!-- <td style="background: linear-gradient(135deg, #1f3a93 0%, #3498db 100%); padding:20px 30px;"> -->
            <td style="background: linear-gradient(180deg, #570a0aff, #932828 50%, #9f3030 100%); padding:20px 30px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td align="left" style="vertical-align: middle;">
                    <img src="{{ $message->embed(public_path('logo.png')) }}" alt="EnrollSys Logo" style="height:100px; display:block;">
                  </td>
                  <td align="right" style="vertical-align: middle;">
                    <h1 style="font-family:'Montserrat', sans-serif; font-size:28px; font-weight:700; color:#ffffff; margin:0; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                      EnrollSys
                    </h1>
                    <p style="font-size:14px; color:#e8f4fc; margin:5px 0 0 0; font-weight:300;">
                      EVSU Ormoc Campus
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px 30px;">
              <p style="margin:0 0 15px 0; font-size:16px; font-weight:500;">Dear User,</p>

              <p style="margin:0 0 25px 0; font-size:15px; color:#555; font-weight:400;">
                You recently requested to reset your password for your <strong style="color:#1f3a93;">EnrollSys</strong> account. Please use the verification code below to complete the process:
              </p>

              <!-- Code Section -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:30px 0;">
                <tr>
                  <td align="center" style="background-color:#f9fbfd; border:2px dashed #3498db; padding:30px; border-radius:12px;">
                    <h3 style="margin:0 0 15px 0; font-weight:600; color:#2c3e50; font-family:'Montserrat', sans-serif; font-size:18px;">
                      Your Verification Code
                    </h3>
                    <div style="font-size:36px; font-weight:700; letter-spacing:8px; color:#1f3a93; padding:15px 25px; background:#ffffff; border-radius:8px; display:inline-block; font-family:'Montserrat', sans-serif; border:1px solid #e1e8ed;">
                      {{ $otp }}
                    </div>
                    <p style="margin:15px 0 0 0; font-size:13px; color:#7f8c8d;">
                      <!-- This code expires in 15 minutes -->
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Security Note -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:25px 0;">
                <tr>
                  <td style="background-color:#fff8e6; border-left:4px solid #ffa726; padding:18px 20px; border-radius:8px; font-size:14px; color:#6b4f00;">
                    <strong style="font-weight:600;">🔒 Security Note:</strong> If you did not request this password reset, please ignore this email or contact support immediately.
                  </td>
                </tr>
              </table>

              <p style="margin:25px 0; font-size:14px; color:#555; font-weight:400;">
                For assistance, please contact our support team at 
                <a href="mailto:support@evsu.ormoc.ph" style="color:#3498db; text-decoration:none; font-weight:500;">support@evsu.ormoc.ph</a>.
              </p>

              <!-- Signature -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:30px; border-top:1px solid #eeeeee;">
                <tr>
                  <td style="padding-top:20px;">
                    <p style="margin:0; font-size:14px; color:#444; font-weight:400;">
                      Best regards,<br>
                      <strong style="font-family:'Montserrat', sans-serif; font-weight:600; color:#1f3a93;">Team CyberNexus (EnrollSys)</strong><br>
                      EVSU Ormoc Campus
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="background: linear-gradient(180deg, #570a0aff, #932828 50%, #9f3030 100%); padding:25px; font-size:12px; color:#ffffff;">
              <p style="margin:0; font-weight:300;">© {{ date('Y') }} EnrollSys - EVSU Ormoc Campus. All rights reserved.</p>
              <p style="margin:8px 0; font-weight:300;">This is an automated message. Please do not reply.</p>
              <p style="margin:8px 0;">
                <!-- <a href="#" style="color:#a3d0fd; text-decoration:none; font-weight:400;">Privacy Policy</a> • 
                <a href="#" style="color:#a3d0fd; text-decoration:none; font-weight:400;">Terms of Service</a> -->
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