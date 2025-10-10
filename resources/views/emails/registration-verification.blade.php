<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Verification - EnrollSys</title>
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
            <td style="background: linear-gradient(135deg, #1f3a93 0%, #3498db 100%); padding:25px 30px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td align="left" style="vertical-align: middle;">
                    <img src="{{ $message->embed(public_path('logo.png')) }}" alt="EnrollSys Logo" style="height:70px; display:block;">
                  </td>
                  <td align="right" style="vertical-align: middle;">
                    <h1 style="font-family:'Montserrat', sans-serif; font-size:32px; font-weight:700; color:#ffffff; margin:0; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                      EnrollSys
                    </h1>
                    <p style="font-size:15px; color:#e8f4fc; margin:5px 0 0 0; font-weight:300; letter-spacing:0.5px;">
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
              @if($givenName)
              <p style="margin:0 0 15px 0; font-size:17px; font-weight:500; color:#2c3e50;">Dear {{ $givenName }},</p>
              @else
              <p style="margin:0 0 15px 0; font-size:17px; font-weight:500; color:#2c3e50;">Dear Student,</p>
              @endif

              <p style="margin:0 0 25px 0; font-size:15px; color:#555; font-weight:400;">
                Welcome to <strong style="color:#1f3a93;">EnrollSys</strong>! We're excited to have you on board. To complete your registration and verify your email address, please use the verification code below:
              </p>

              <!-- Code Section -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:30px 0;">
                <tr>
                  <td align="center" style="background-color:#f9fbfd; border:2px dashed #3498db; padding:30px; border-radius:12px;">
                    <h3 style="margin:0 0 15px 0; font-weight:600; color:#2c3e50; font-family:'Montserrat', sans-serif; font-size:18px;">
                      Email Verification Code
                    </h3>
                    <div style="font-size:36px; font-weight:700; letter-spacing:8px; color:#1f3a93; padding:15px 25px; background:#ffffff; border-radius:8px; display:inline-block; font-family:'Montserrat', sans-serif; border:1px solid #e1e8ed; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                      {{ $verificationCode }}
                    </div>
                    <p style="margin:15px 0 0 0; font-size:13px; color:#7f8c8d; font-weight:400;">
                      <!-- This code will expire in 10 minutes -->
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Next Steps -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0;">
                <tr>
                  <td style="background-color:#e8f4fd; border-left:4px solid #3498db; padding:18px 20px; border-radius:8px; font-size:14px; color:#2c3e50;">
                    <strong style="font-weight:600; display:block; margin-bottom:5px;">📝 Next Steps:</strong>
                    Enter this code in the verification field to complete your account setup and start using EnrollSys.
                  </td>
                </tr>
              </table>

              <!-- Security Note -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0;">
                <tr>
                  <td style="background-color:#fff8e6; border-left:4px solid #ffa726; padding:18px 20px; border-radius:8px; font-size:14px; color:#6b4f00;">
                    <strong style="font-weight:600;">🔒 Security Note:</strong> If you did not request this registration, please ignore this email. Your email address will not be used for any purpose.
                  </td>
                </tr>
              </table>

              <p style="margin:25px 0; font-size:14px; color:#555; font-weight:400;">
                Need help? Contact our support team at 
                <a href="mailto:support@evsu.ormoc.ph" style="color:#3498db; text-decoration:none; font-weight:500;">support@evsu.ormoc.ph</a>.
              </p>

              <!-- Signature -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:30px; border-top:1px solid #eeeeee;">
                <tr>
                  <td style="padding-top:20px;">
                    <p style="margin:0; font-size:14px; color:#444; font-weight:400;">
                      Welcome aboard,<br>
                      <strong style="font-family:'Montserrat', sans-serif; font-weight:600; color:#1f3a93;">Team CyberNexus</strong><br>
                      EVSU Ormoc Campus
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="background-color:#1f3a93; padding:25px; font-size:12px; color:#ffffff;">
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