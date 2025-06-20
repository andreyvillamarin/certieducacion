<?php
// admin/certificate_template.php (Diseño Idéntico al PDF Final)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Asistencia</title>
    <style>
        body { font-family: 'times', serif; margin: 0; padding: 0; background-color: #fff; color: #000; }
        .certificate-container { width: 794px; height: 1123px; margin: auto; padding: 40px 60px; position: relative; box-sizing: border-box; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img.logo { width: 200px; margin-bottom: 20px; }
        .header .slogan { font-family: 'helvetica', sans-serif; font-size: 14px; font-weight: bold; color: #333; margin-bottom: 20px;}
        .header-line { border-bottom: 2px solid #000; }
        .resolution { text-align: center; font-family: 'helvetica', sans-serif; font-size: 10px; font-weight: bold; margin-top: 20px; margin-bottom: 80px; }
        .main-content { text-align: center; }
        .main-content .intro-text { font-size: 20px; font-style: italic; margin-bottom: 40px; }
        .student-name-container { border-top: 2px dashed #000; border-bottom: 2px dashed #000; padding: 10px 0; margin: 20px auto; width: 80%; }
        .main-content .student-name { font-size: 32px; font-weight: bold; text-transform: uppercase; }
        .main-content .student-id { font-size: 16px; font-weight: normal; text-transform: none; margin-top: 5px; }
        .main-content .attended-text { font-size: 18px; margin-top: 40px; font-style: italic;}
        .main-content .course-name { font-size: 24px; font-weight: bold; text-transform: uppercase; margin-top: 20px; }
        .main-content .duration-text { font-size: 16px; margin-top: 20px; font-style: italic;}
        .date-section { text-align: center; margin-top: 80px; font-size: 16px; font-style: italic;}
        .footer-table { width: 100%; position: absolute; bottom: 40px; left: 0; right: 0; border-collapse: collapse; }
        .footer-table td { vertical-align: bottom; text-align: center; padding: 0 10px; }
        .signature-block { width: 300px; margin: 0 auto; position: relative; height: 120px; }
        .signature-image { position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); max-height: 80px; max-width: 250px; }
        .signature-line { position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #000; padding-top: 8px; font-family: 'helvetica', sans-serif; font-size: 12px; font-weight: bold; }
        .vigilado-logo { width: 120px; }
        .qr-code { width: 80px; height: 80px; }
        .qr-text { font-family: 'helvetica', sans-serif; font-size: 9px; color: #555; display: block; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="certificate-container">
        
        <div class="header">
            <img src="{{logo_path}}" alt="Logo" class="logo">
            <div class="slogan">Por tu progreso, todo</div>
            <div class="header-line"></div>
        </div>

        <div class="resolution">
            APROBADO POR LA SECRETARÍA DE EDUCACIÓN MUNICIPAL<br>
            RESOLUCIÓN DE RENOVACIÓN 0153 DE 2012
        </div>

        <div class="main-content">
            <div class="intro-text">Hace constar que:</div>
            
            <div class="student-name-container">
                 <div class="student-name">{{student_name}}</div>
                 <div class="student-id">C.C. No. {{student_identification}}</div>
            </div>
            
            <div class="attended-text">Asistió a:</div>
            <div class="course-name">{{course_name}}</div>
            <div class="duration-text">Con una intensidad de <strong>{{duration}}</strong> horas</div>
        </div>

        <div class="date-section">Dado en Neiva a los {{issue_date}}</div>

        <table class="footer-table">
            <tr>
                <td style="width: 30%; text-align: left; padding-left: 60px;">
                    <img src="{{qr_code_path}}" alt="QR Code" class="qr-code">
                    <span class="qr-text">Código: {{validation_code}}</span>
                </td>
                <td style="width: 40%;">
                    <div class="signature-block">
                        <img src="{{signature_path}}" alt="Firma" class="signature-image">
                        <div class="signature-line">
                            {{director_name}}<br>
                            JEFE DE DIVISIÓN SERVICIOS EDUCATIVOS
                        </div>
                    </div>
                </td>
                <td style="width: 30%; text-align: right; padding-right: 60px;">
                    <img src="{{vigilado_logo_path}}" alt="Vigilado SuperSubsidio" class="vigilado-logo">
                </td>
            </tr>
        </table>
    </div>
</body>
</html>