<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Verification - EnrollSys</title>
</head>
<body style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height:1.6; color:#333333; margin:0; padding:0; background-color:#f4f6f8;">

  <!-- Wrapper -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="padding:30px 0;">
    <tr>
      <td align="center">

        <!-- Main Container -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.08); overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td align="center" style="background:linear-gradient(135deg, #1f3a93 0%, #3498db 100%); height:100px; padding:0px;">
              <img src="{{ $message->embed(public_path('logo.png')) }}" alt="EnrollSys Logo" style="height:200px; display:block; margin:0 auto;">
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:30px;">
              @if($givenName)
              <p style="margin:0 0 15px 0; font-size:16px;">Dear {{ $givenName }},</p>
              @else
              <p style="margin:0 0 15px 0; font-size:16px;">Dear Student,</p>
              @endif

              <p style="margin:0 0 25px 0; font-size:15px; color:#555;">
                Welcome to EnrollSys! To complete your registration, please use the verification code below:
              </p>

              <!-- Code Section -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:25px 0;">
                <tr>
                  <td align="center" style="background-color:#f9fbfd; border:2px dashed #3498db; padding:25px; border-radius:8px;">
                    <h3 style="margin:0 0 10px 0; font-weight:500; color:#2c3e50;">Your Verification Code</h3>
                    <div style="font-size:32px; font-weight:bold; letter-spacing:6px; color:#1f3a93; padding:12px 20px; background:#ffffff; border-radius:6px; display:inline-block;">
                      {{ $verificationCode }}
                    </div>
                    <p style="margin:15px 0 0 0; font-size:14px; color:#666;">
                      <!-- This code will expire in 10 minutes -->
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Security Note -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0;">
                <tr>
                  <td style="background-color:#fff8e6; border-left:4px solid #ffa726; padding:15px 18px; border-radius:6px; font-size:14px; color:#6b4f00;">
                    <strong>Security Note:</strong> If you did not request this registration, please ignore this email.
                  </td>
                </tr>
              </table>

              <p style="margin:25px 0; font-size:14px; color:#555;">
                For assistance, please contact our support team at 
                <a href="mailto:support@evsu.ormoc.ph" style="color:#3498db; text-decoration:none;">support@evsu.ormoc.ph</a>.
              </p>

              <!-- Signature -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:25px; border-top:1px solid #eeeeee;">
                <tr>
                  <td style="padding-top:18px;">
                    <p style="margin:0; font-size:14px; color:#444;">
                      Best regards,<br>
                      <strong>Team CyberNexus</strong><br>
                      EVSU Ormoc Campus
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="background-color:#f4f6f8; padding:20px; font-size:12px; color:#888;">
              <p style="margin:0;">© {{ date('Y') }} EnrollSys - EVSU Ormoc Campus. All rights reserved.</p>
              <p style="margin:5px 0;">This is an automated message. Please do not reply.</p>
              <p style="margin:5px 0;">
                <!-- <a href="#" style="color:#3498db; text-decoration:none;">Privacy Policy</a> • 
                <a href="#" style="color:#3498db; text-decoration:none;">Terms of Service</a> -->
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