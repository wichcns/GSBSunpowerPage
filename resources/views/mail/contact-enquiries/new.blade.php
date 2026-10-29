@php($services = implode(', ', $contactEnquiry->services ?? []) ?: '—')
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ข้อมูลติดต่อใหม่ | GSB SunPower</title>
</head>
<body style="margin:0;padding:0;background:#eef5f3;color:#193b40;font-family:Arial,'Noto Sans Thai',sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#eef5f3;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px;max-width:100%;overflow:hidden;border-radius:20px;background:#ffffff;box-shadow:0 12px 40px rgba(6,59,66,.12);">
                    <tr>
                        <td align="center" style="padding:26px 34px 22px;background:#063b42;">
                            <img src="{{ $message->embed($logoPath) }}" width="154" alt="GSB SunPower Group" style="display:block;width:154px;max-width:100%;height:auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 34px 12px;">
                            <p style="margin:0 0 10px;color:#00a88f;font-size:11px;font-weight:700;letter-spacing:1.6px;line-height:1.4;">NEW WEBSITE ENQUIRY</p>
                            <h1 style="margin:0;color:#073b42;font-size:27px;line-height:1.25;letter-spacing:-.4px;">มีข้อมูลติดต่อใหม่<br>จากเว็บไซต์</h1>
                            <p style="margin:17px 0 0;color:#587378;font-size:14px;line-height:1.7;">เรียนทีมการตลาด GSB SunPower<br>มีผู้สนใจส่งข้อมูลผ่านเว็บไซต์ กรุณาตรวจสอบรายละเอียดและติดต่อกลับตามความเหมาะสม</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 34px 12px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border:1px solid #dce9e6;border-radius:12px;border-collapse:separate;overflow:hidden;">
                                <tr><td colspan="2" style="padding:12px 16px;background:#f1faf8;color:#0a786b;font-size:12px;font-weight:700;letter-spacing:.5px;">รายละเอียดผู้ติดต่อ</td></tr>
                                <tr><td style="width:37%;padding:11px 16px;border-top:1px solid #e5efed;color:#698288;font-size:12px;">ชื่อ</td><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#163e44;font-size:13px;font-weight:700;">{{ $contactEnquiry->name }}</td></tr>
                                <tr><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#698288;font-size:12px;">บริษัท</td><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#163e44;font-size:13px;">{{ $contactEnquiry->company ?: '—' }}</td></tr>
                                <tr><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#698288;font-size:12px;">อีเมล</td><td style="padding:11px 16px;border-top:1px solid #e5efed;font-size:13px;"><a href="mailto:{{ $contactEnquiry->email }}" style="color:#008d7d;text-decoration:none;font-weight:700;">{{ $contactEnquiry->email }}</a></td></tr>
                                <tr><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#698288;font-size:12px;">เบอร์โทรศัพท์</td><td style="padding:11px 16px;border-top:1px solid #e5efed;font-size:13px;"><a href="tel:{{ $contactEnquiry->phone }}" style="color:#163e44;text-decoration:none;font-weight:700;">{{ $contactEnquiry->phone }}</a></td></tr>
                                <tr><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#698288;font-size:12px;">บริการที่สนใจ</td><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#163e44;font-size:13px;">{{ $services }}</td></tr>
                                <tr><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#698288;font-size:12px;">สถานที่โครงการ</td><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#163e44;font-size:13px;">{{ $contactEnquiry->project_location ?: '—' }}</td></tr>
                                <tr><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#698288;font-size:12px;">งบประมาณ</td><td style="padding:11px 16px;border-top:1px solid #e5efed;color:#163e44;font-size:13px;">{{ $contactEnquiry->budget ?: '—' }}</td></tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 34px 8px;">
                            <p style="margin:0 0 8px;color:#0a786b;font-size:12px;font-weight:700;letter-spacing:.4px;">รายละเอียดเพิ่มเติม</p>
                            <div style="padding:15px 16px;border-left:3px solid #55dec5;background:#f6faf9;color:#4d696e;font-size:13px;line-height:1.75;white-space:pre-line;">{{ $contactEnquiry->message ?: 'ไม่มีรายละเอียดเพิ่มเติม' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:25px 34px 32px;">
                            <a href="{{ route('admin.contact-enquiries.show', $contactEnquiry) }}" style="display:inline-block;padding:13px 24px;border-radius:999px;background:#00af97;color:#063b42;font-size:13px;font-weight:700;text-decoration:none;">เปิดดูข้อมูลในระบบ Admin&nbsp;&nbsp;↗</a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 28px;border-top:1px solid #dce9e6;background:#f8fbfa;color:#71868a;font-size:11px;line-height:1.65;">
                            อีเมลนี้ส่งโดยอัตโนมัติจากเว็บไซต์ GSB SunPower Group<br>
                            BHIRAJ TOWER at BITEC, 22nd Floor, Room 2209, Bangkok 10260, Thailand
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
