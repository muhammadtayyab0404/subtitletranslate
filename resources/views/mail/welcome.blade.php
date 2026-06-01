<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OTP Verification</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">

  <!-- Wrapper -->
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:40px 0;">
    <tr>
      <td align="center">

        <!-- Card -->
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.08);">
          
          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#3b82f6,#6366f1); padding:24px; text-align:center;">
              <h1 style="margin:0; font-size:22px; color:#ffffff;">
                SubtitleAI
              </h1>
              <p style="margin:6px 0 0; font-size:14px; color:#e0e7ff;">
                OTP Verification
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:28px; text-align:center;">
              <h2 style="margin:0 0 10px; font-size:20px; color:#111827;">
                Verify Your Email
              </h2>

              <p style="margin:0 0 24px; font-size:14px; color:#6b7280;">
                Please use the OTP below to complete your registration.
                This code is valid for <strong>5 minutes</strong>.
              </p>

              <!-- OTP Box -->
              <div style="
                display:inline-block;
                padding:14px 28px;
                font-size:28px;
                font-weight:bold;
                letter-spacing:6px;
                color:#1e40af;
                background:#eef2ff;
                border-radius:12px;
                margin-bottom:20px;">
                {{ $otp }}
              </div>

              <p style="margin:0; font-size:13px; color:#6b7280;">
                If you did not request this, please ignore this email.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f9fafb; padding:16px; text-align:center;">
              <p style="margin:0; font-size:12px; color:#9ca3af;">
                © {{ date('Y') }} SubtitleAI. All rights reserved.
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
