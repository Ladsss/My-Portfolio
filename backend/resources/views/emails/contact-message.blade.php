<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $subjectLine }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { padding: 20px 12px !important; }
            .email-card { width: 100% !important; }
            .email-content { padding: 24px 20px !important; }
            .brand-header { padding: 24px 20px !important; }
            .detail-label { width: 100px !important; padding-left: 12px !important; }
            .detail-value { padding-right: 12px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f8; color:#202235; font-family:Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; mso-hide:all;">
        New portfolio inquiry from {{ $senderName }} — {{ $subjectLine }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; background-color:#f3f4f8;">
        <tr>
            <td class="email-shell" align="center" style="padding:40px 16px;">
                <table class="email-card" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; border-collapse:separate; border-spacing:0;">
                    <tr>
                        <td class="brand-header" style="padding:28px 32px; border-radius:12px 12px 0 0; background-color:#17152b; border-bottom:3px solid #7955c8;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <tr>
                                    <td valign="middle" style="width:42px;">
                                        <table role="presentation" width="38" height="38" cellpadding="0" cellspacing="0" border="0" style="width:38px; height:38px; border-collapse:separate; border-spacing:0;">
                                            <tr>
                                                <td align="center" valign="middle" style="border-radius:8px; background-color:#7955c8; color:#ffffff; font-size:14px; font-weight:bold; letter-spacing:1px;">RE</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td valign="middle" style="padding-left:12px;">
                                        <p style="margin:0; color:#ffffff; font-size:14px; font-weight:bold; letter-spacing:0.7px; line-height:1.4;">REYMART EPAN</p>
                                        <p style="margin:3px 0 0; color:#c5bfd7; font-size:12px; line-height:1.4;">Full-Stack Developer &amp; QA Automation Engineer</p>
                                    </td>
                                    <td align="right" valign="middle" style="color:#c5bfd7; font-size:11px; line-height:1.4;">PORTFOLIO<br>CONTACT</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" style="padding:32px; border:1px solid #e3e3eb; border-top:0; border-radius:0 0 12px 12px; background-color:#ffffff;">
                            <p style="margin:0 0 7px; color:#7955c8; font-size:11px; font-weight:bold; letter-spacing:1.2px; line-height:1.5; text-transform:uppercase;">New inquiry</p>
                            <h1 style="margin:0 0 8px; color:#202235; font-size:22px; font-weight:bold; line-height:1.35;">{{ $subjectLine }}</h1>
                            <p style="margin:0; color:#6d6c7d; font-size:14px; line-height:1.6;">A message was submitted through your portfolio contact form.</p>

                            <h2 style="margin:28px 0 10px; color:#333247; font-size:13px; font-weight:bold; letter-spacing:0.5px; line-height:1.5;">SENDER DETAILS</h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border:1px solid #e8e7ef; border-collapse:separate; border-spacing:0; background-color:#faf9fc;">
                                <tr>
                                    <td class="detail-label" width="118" valign="top" style="width:118px; padding:13px 16px; border-bottom:1px solid #e8e7ef; color:#6d6c7d; font-size:12px; font-weight:bold; line-height:1.5;">Name</td>
                                    <td class="detail-value" valign="top" style="padding:13px 16px; border-bottom:1px solid #e8e7ef; color:#202235; font-size:14px; line-height:1.5; overflow-wrap:anywhere; word-break:break-word;">{{ $senderName }}</td>
                                </tr>
                                <tr>
                                    <td class="detail-label" width="118" valign="top" style="width:118px; padding:13px 16px; border-bottom:1px solid #e8e7ef; color:#6d6c7d; font-size:12px; font-weight:bold; line-height:1.5;">Email Address</td>
                                    <td class="detail-value" valign="top" style="padding:13px 16px; border-bottom:1px solid #e8e7ef; color:#5f3da8; font-size:14px; line-height:1.5; overflow-wrap:anywhere; word-break:break-word;">
                                        <a href="mailto:{{ $senderEmail }}" style="color:#5f3da8; text-decoration:underline;">{{ $senderEmail }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="detail-label" width="118" valign="top" style="width:118px; padding:13px 16px; color:#6d6c7d; font-size:12px; font-weight:bold; line-height:1.5;">Subject</td>
                                    <td class="detail-value" valign="top" style="padding:13px 16px; color:#202235; font-size:14px; line-height:1.5; overflow-wrap:anywhere; word-break:break-word;">{{ $subjectLine }}</td>
                                </tr>
                            </table>

                            <h2 style="margin:28px 0 10px; color:#333247; font-size:13px; font-weight:bold; letter-spacing:0.5px; line-height:1.5;">MESSAGE</h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse;">
                                <tr>
                                    <td style="padding:18px 20px; border:1px solid #e8e7ef; border-left:3px solid #7955c8; background-color:#faf9fc; color:#333247; font-size:14px; line-height:1.75; overflow-wrap:anywhere; word-break:break-word;">{!! nl2br(e($messageBody)) !!}</td>
                                </tr>
                            </table>
                            <p style="margin:20px 0 0; color:#6d6c7d; font-size:12px; line-height:1.6;">
                                Reply to this email to respond to
                                <a href="mailto:{{ $senderEmail }}" style="color:#5f3da8; text-decoration:underline;">{{ $senderEmail }}</a>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 16px 0; color:#6d6c7d; font-size:12px; line-height:1.6;">
                            <strong style="color:#333247;">Reymart Jay DG. Epan</strong><br>
                            Portfolio contact notification
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
