<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Question Received - EnrollSys</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Inline styles for email compatibility */
    body {
      font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: #333333;
      margin: 0;
      padding: 0;
      background-color: #f4f6f8;
    }
  </style>
</head>
<body>
  <!-- Wrapper -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="padding:30px 0;">
    <tr>
      <td align="center">
        <!-- Main Container -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.08); overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td style="background: linear-gradient(180deg, #570a0aff, #932828 50%, #9f3030 100%); padding:25px 30px;">
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

          <!-- Body Content -->
          <tr>
            <td style="padding:40px 30px;">
              <p style="margin:0 0 15px 0; font-size:17px; font-weight:500; color:#2c3e50;">Dear Admin,</p>
              
              <p style="margin:0 0 20px 0; font-size:15px; color:#555; font-weight:400;">
                You have received a new question from a user:
              </p>
              
              <div style="background:#f8f9fa; border-left:4px solid #9f3030; padding:15px; margin:20px 0; border-radius:4px;">
                <p style="margin:0; font-size:15px; color:#444; font-weight:400; font-style:italic;">
                  "{{ $q_ms }}"
                </p>
              </div>
              
              <!-- Sender Information -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:30px; border-top:1px solid #eeeeee; padding-top:20px;">
                <tr>
                  <td>
                    <p style="margin:0 0 8px 0; font-size:14px; color:#444; font-weight:400;">
                      <strong>Subject:</strong> {{ $q_subject }}
                    </p>
                    <p style="margin:0 0 8px 0; font-size:14px; color:#444; font-weight:400;">
                      <strong>From:</strong> {{ $q_name }}
                    </p>
                    <p style="margin:0; font-size:14px; color:#444; font-weight:400;">
                      <strong>Email:</strong> {{ $q_email }}
                    </p>
                  </td>
                </tr>
              </table>
              
              <div style="margin-top:30px; padding-top:20px; border-top:1px solid #eeeeee;">
                <p style="margin:0 0 10px 0; font-size:14px; color:#666; font-weight:400;">
                  Please respond to this inquiry as soon as possible.
                </p>
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="background: linear-gradient(180deg, #570a0aff, #932828 50%, #9f3030 100%); padding:25px; font-size:12px; color:#ffffff;">
              <p style="margin:0; font-weight:300;">© {{ date('Y') }} EnrollSys - EVSU Ormoc Campus. All rights reserved.</p>
              <p style="margin:8px 0; font-weight:300;">This is an automated message. Please do not reply.</p>
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