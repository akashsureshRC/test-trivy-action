<?php

namespace Database\Seeders\Hrm;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $emailTemplate = [
            'New User',
            'New Payroll',
            'Employee Leave Received',
            'Employee Leave Cancelled',
            'Leave Request Approved',
            'Leave Request Rejected',
            'New Helpdesk Ticket Reply',
        ];
        $defaultTemplate = [
            'New User' => [
                'subject' => 'Welcome - Your Account Has Been Created',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "Email": "email",
                    "Password": "password"
                  }',
                'lang' => [
                    'ar' => '<p><strong>الموضوع:</strong> مرحبًا بك في {app_name}</p>
                    <p>مرحبًا {company_name}،</p>
                    <p>تم إنشاء حسابك بنجاح.</p>
                    <p>فيما يلي بيانات تسجيل الدخول الخاصة بك:</p>
                    <p><strong>البريد الإلكتروني:</strong> {email}<br><strong>كلمة المرور:</strong> {password}</p>
                    <p>يرجى تسجيل الدخول وتغيير كلمة المرور في أقرب وقت ممكن.</p>
                    <p>لا تتردد في التواصل معنا إذا كان لديك أي أسئلة.</p>
                    <p>شكرًا لك</p>
                    <p><strong>مع التحية،</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'da' => '<p><strong>Emne:</strong> Velkommen til {app_name}</p>
                    <p>Hej {company_name},</p>
                    <p>Din konto er blevet oprettet.</p>
                    <p>Her er dine loginoplysninger:</p>
                    <p><strong>E-mail:</strong> {email}<br><strong>Adgangskode:</strong> {password}</p>
                    <p>Log venligst ind og skift din adgangskode hurtigst muligt.</p>
                    <p>Du er velkommen til at kontakte os, hvis du har spørgsmål.</p>
                    <p>Tak</p>
                    <p><strong>Med venlig hilsen,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'de' => '<p><strong>Betreff:</strong> Willkommen bei {app_name}</p>
                    <p>Hallo {company_name},</p>
                    <p>Ihr Konto wurde erfolgreich erstellt.</p>
                    <p>Hier sind Ihre Anmeldedaten:</p>
                    <p><strong>E-Mail:</strong> {email}<br><strong>Passwort:</strong> {password}</p>
                    <p>Bitte melden Sie sich an und ändern Sie Ihr Passwort so bald wie möglich.</p>
                    <p>Zögern Sie nicht, uns bei Fragen zu kontaktieren.</p>
                    <p>Vielen Dank</p>
                    <p><strong>Mit freundlichen Grüßen,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'en' => '<p><strong>Subject:</strong> Welcome to {app_name}</p>
                    <p>Hi {company_name},</p>
                    <p>Your account has been created successfully.</p>
                    <p>Here are your login details:</p>
                    <p><strong>Email:</strong> {email}<br><strong>Password:</strong> {password}</p>
                    <p>Please log in and change your password as soon as possible.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'es' => '<p><strong>Asunto:</strong> Bienvenido a {app_name}</p>
                    <p>Hola {company_name},</p>
                    <p>Su cuenta ha sido creada exitosamente.</p>
                    <p>Aquí están sus datos de inicio de sesión:</p>
                    <p><strong>Correo electrónico:</strong> {email}<br><strong>Contraseña:</strong> {password}</p>
                    <p>Inicie sesión y cambie su contraseña lo antes posible.</p>
                    <p>No dude en contactarnos si tiene alguna pregunta.</p>
                    <p>Gracias</p>
                    <p><strong>Saludos,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'fr' => '<p><strong>Objet :</strong> Bienvenue sur {app_name}</p>
                    <p>Bonjour {company_name},</p>
                    <p>Votre compte a été créé avec succès.</p>
                    <p>Voici vos identifiants de connexion :</p>
                    <p><strong>E-mail :</strong> {email}<br><strong>Mot de passe :</strong> {password}</p>
                    <p>Veuillez vous connecter et modifier votre mot de passe dès que possible.</p>
                    <p>N\'hésitez pas à nous contacter si vous avez des questions.</p>
                    <p>Merci</p>
                    <p><strong>Cordialement,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'it' => '<p><strong>Oggetto:</strong> Benvenuto su {app_name}</p>
                    <p>Ciao {company_name},</p>
                    <p>Il tuo account è stato creato con successo.</p>
                    <p>Ecco i tuoi dati di accesso:</p>
                    <p><strong>Email:</strong> {email}<br><strong>Password:</strong> {password}</p>
                    <p>Accedi e cambia la tua password il prima possibile.</p>
                    <p>Non esitare a contattarci per qualsiasi domanda.</p>
                    <p>Grazie</p>
                    <p><strong>Cordiali saluti,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'ja' => '<p><strong>件名:</strong> {app_name}へようこそ</p>
                    <p>{company_name} 様</p>
                    <p>アカウントが正常に作成されました。</p>
                    <p>ログイン情報は以下の通りです：</p>
                    <p><strong>メール:</strong> {email}<br><strong>パスワード:</strong> {password}</p>
                    <p>できるだけ早くログインしてパスワードを変更してください。</p>
                    <p>ご質問がございましたら、お気軽にお問い合わせください。</p>
                    <p>ありがとうございます</p>
                    <p><strong>よろしくお願いいたします、</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'nl' => '<p><strong>Onderwerp:</strong> Welkom bij {app_name}</p>
                    <p>Hallo {company_name},</p>
                    <p>Uw account is succesvol aangemaakt.</p>
                    <p>Hier zijn uw inloggegevens:</p>
                    <p><strong>E-mail:</strong> {email}<br><strong>Wachtwoord:</strong> {password}</p>
                    <p>Log in en wijzig uw wachtwoord zo snel mogelijk.</p>
                    <p>Neem gerust contact met ons op als u vragen heeft.</p>
                    <p>Bedankt</p>
                    <p><strong>Met vriendelijke groet,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'pl' => '<p><strong>Temat:</strong> Witamy w {app_name}</p>
                    <p>Witaj {company_name},</p>
                    <p>Twoje konto zostało pomyślnie utworzone.</p>
                    <p>Oto Twoje dane logowania:</p>
                    <p><strong>E-mail:</strong> {email}<br><strong>Hasło:</strong> {password}</p>
                    <p>Zaloguj się i zmień hasło tak szybko, jak to możliwe.</p>
                    <p>Skontaktuj się z nami, jeśli masz jakiekolwiek pytania.</p>
                    <p>Dziękujemy</p>
                    <p><strong>Z poważaniem,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'pt' => '<p><strong>Assunto:</strong> Bem-vindo ao {app_name}</p>
                    <p>Olá {company_name},</p>
                    <p>Sua conta foi criada com sucesso.</p>
                    <p>Aqui estão seus dados de login:</p>
                    <p><strong>E-mail:</strong> {email}<br><strong>Senha:</strong> {password}</p>
                    <p>Faça login e altere sua senha o mais rápido possível.</p>
                    <p>Sinta-se à vontade para entrar em contato se tiver alguma dúvida.</p>
                    <p>Obrigado</p>
                    <p><strong>Atenciosamente,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'ru' => '<p><strong>Тема:</strong> Добро пожаловать в {app_name}</p>
                    <p>Здравствуйте {company_name},</p>
                    <p>Ваша учётная запись была успешно создана.</p>
                    <p>Вот ваши данные для входа:</p>
                    <p><strong>Электронная почта:</strong> {email}<br><strong>Пароль:</strong> {password}</p>
                    <p>Пожалуйста, войдите в систему и измените пароль как можно скорее.</p>
                    <p>Не стесняйтесь обращаться к нам, если у вас есть вопросы.</p>
                    <p>Спасибо</p>
                    <p><strong>С уважением,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'af' => '<p><strong>Onderwerp:</strong> Welkom by {app_name}</p>
                    <p>Hallo {company_name},</p>
                    <p>Jou rekening is suksesvol geskep.</p>
                    <p>Hier is jou aanmeldbesonderhede:</p>
                    <p><strong>E-pos:</strong> {email}<br><strong>Wagwoord:</strong> {password}</p>
                    <p>Meld asseblief aan en verander jou wagwoord so gou as moontlik.</p>
                    <p>Kontak ons gerus as jy enige vrae het.</p>
                    <p>Dankie</p>
                    <p><strong>Groete,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'zu' => '<p><strong>Isihloko:</strong> Siyakwamukela ku-{app_name}</p>
                    <p>Sawubona {company_name},</p>
                    <p>I-akhawunti yakho idalwe ngempumelelo.</p>
                    <p>Nayi imininingwane yakho yokungena:</p>
                    <p><strong>I-imeyili:</strong> {email}<br><strong>Iphasiwedi:</strong> {password}</p>
                    <p>Sicela ungene bese ushintsha iphasiwedi yakho ngokushesha.</p>
                    <p>Ungasithinta noma nini uma unemibuzo.</p>
                    <p>Siyabonga</p>
                    <p><strong>Ozithobayo,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'New Payroll' => [
                'subject' => 'New Payroll',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "Employee": "name",
                    "Employee Email": "payslip_email",
                    "Salary Month": "salary_month",
                    "URL": "url"
                  }',
                'lang' => [
                    'ar' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Subject :-إدارة الموارد البشرية / الشركة المعنية بإرسال المدفوعات عن طريق البريد الإلكتروني في وقت تأكيد الدفع.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">مرحبا {name} ،</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">أتمنى أن يجدك هذا البريد الإلكتروني جيدا برجاء الرجوع الى الدفع المتصل الى { salary_month&nbsp;}.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">اضغط ببساطة على الاختيار بأسفل</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">كشوف المرتبات</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">إشعر بالحرية للوصول إلى الخارج إذا عندك أي أسئلة.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">شكرا لك</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Regards,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">إدارة الموارد البشرية ،</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{ app_name }</span></p>',
                    'da' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Om: HR-departementet / Kompagniet til at sende l&oslash;nsedler via e-mail p&aring; tidspunktet for bekr&aelig;ftelsen af l&oslash;nsedlerne</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hej {name},</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">H&aring;ber denne e-mail finder dig godt! Se vedh&aelig;ftet payseddel for { salary_month }.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">klik bare p&aring; knappen nedenfor</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">L&oslash;nseddel</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Tak.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Med venlig hilsen</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">HR-afdelingen,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{ app_name }</span></p>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Betrifft: -Personalabteilung/Firma, um Payslips per E-Mail zum Zeitpunkt der Best&auml;tigung des Auszahlungsscheins zu senden</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hi {name},</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hoffe, diese E-Mail findet dich gut! Bitte sehen Sie den angeh&auml;ngten payslip f&uuml;r {salary_month}.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Klicken Sie einfach auf den Button unten</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Payslip</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Danke.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Betrachtet,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Personalabteilung,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_name}</span></p>',
                    'en' => '<p><strong>Subject:</strong> Your Payslip is Ready</p>
                    <p>Hi {name},</p>
                    <p>Hope this email finds you well!</p>
                    <p>Your payslip for <strong>{salary_month}</strong> is now available.</p>
                    <p>To view your payslip, please log in to your <strong>ESS (Employee Self Service) Portal</strong> and navigate to the Payslips section.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p>{app_name}</p>',
                    'es' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Asunto: -Departamento de RRHH/Empresa para enviar n&oacute;minas por correo electr&oacute;nico en el momento de la confirmaci&oacute;n del pago</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hi {name},</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">&iexcl;Espero que este email le encuentre bien! Consulte la ficha de pago adjunta para {salary_month}.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">simplemente haga clic en el bot&oacute;n de abajo</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Payslip</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">&iexcl;Gracias!</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Considerando,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Departamento de Recursos Humanos,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_name}</span></p>',
                    'fr' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Objet: -Ressources humaines / Entreprise pour envoyer des feuillets de paie par courriel au moment de la confirmation du paiement</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Salut {name},</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Jesp&egrave;re que ce courriel vous trouve bien ! Veuillez consulter le bordereau de paie ci-joint pour {salary_month}.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Il suffit de cliquer sur le bouton ci-dessous</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Feuillet de paiement</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Je vous remercie</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Regards,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">D&eacute;partement des RH,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_name}</span></p>',
                    'it' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Oggetto: - Dipartimento HR / Societ&agrave; per inviare busta paga via email al momento della conferma della busta paga</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Ciao {name},</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Spero che questa email ti trovi bene! Si prega di consultare la busta paga per {salary_month}.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">semplicemente clicca sul pulsante sottostante</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Busta paga</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Sentiti libero di raggiungere se hai domande.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Grazie</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Riguardo,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Dipartimento HR,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_name}</span></p>',
                    'ja' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">件名:-HR 部門/企業は、給与明細書の確認時に電子メールで支払いを送信します。</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">こんにちは {name}、</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">この E メールでよくご確認ください。 {salary_month}の添付された payslip を参照してください。</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">下のボタンをクリックするだけで</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">給与明細書</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">質問がある場合は、自由に連絡してください。</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">ありがとう</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">よろしく</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">HR 部門</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_name}</span></p>',
                    'nl' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Betreft: -HR-afdeling/Bedrijf om te betalen payslips per e-mail op het moment van bevestiging van de payslip</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hallo {name},</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hoop dat deze e-mail je goed vindt! Zie bijgevoegde payslip voor { salary_month }.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">gewoon klikken op de knop hieronder</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Loonstrook</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Voel je vrij om uit te reiken als je vragen hebt.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Dank u wel</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Betreft:</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">HR-afdeling,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{ app_name }</span></p>',
                    'pl' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Temat:-Dział HR/Firma do wysyłania payslip&oacute;w drogą mailową w czasie potwierdzania payslipa</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Witaj {name },</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Mam nadzieję, że ta wiadomość znajdzie Cię dobrze! Patrz załączony payslip dla {salary_month }.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">po prostu kliknij na przycisk poniżej</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Payslip</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Czuj się swobodnie, jeśli masz jakieś pytania.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Dziękujemy</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">W odniesieniu do</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Dział HR,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_name }</span></p>',
                    'ru' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Тема: -HR отдел/Компания для отправки паузу по электронной почте во время подтверждения паузли</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Привет {name},</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Надеюсь, это электронное письмо найдет вас хорошо! См. вложенный раздел для { salary_month }.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">просто нажмите на кнопку внизу</span></p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Паушлип</strong> </a></span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Не стеснитесь, если у вас есть вопросы.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Спасибо.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">С уважением,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Отдел кадров,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{ app_name }</span></p>',
                    'pt' => '<p>Assunto:-Departamento de RH / Empresa para enviar payslips por e-mail no momento da confirma&ccedil;&atilde;o do payslip</p>
                    <p>Oi {name},</p>
                    <p>Espero que este e-mail encontre voc&ecirc; bem! Por favor, consulte o payslip anexado por {salary_month}.</p>
                    <p>basta clicar no bot&atilde;o abaixo</p>
                    <p style="text-align: center;" align="center"><span style="font-size: 18pt;"><a style="background: #6676ef; color: #ffffff; font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; line-height: 120%; margin: 0px; text-decoration: none; text-transform: none;" href="{url}" target="_blank" rel="noopener"> <strong style="color: white; font-weight: bold; text: white;">Payslip</strong> </a></span></p>
                    <p>Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</p>
                    <p>Obrigado</p>
                    <p>Considera,</p>
                    <p>Departamento de RH,</p>
                    <p>{app_name}</p>',
                ],
            ],
            'Employee Leave Received' => [
                'subject' => 'Employee Leave Received',
                'variables' => '{ "App Url": "app_url", "App Name": "app_name", "Company Name": "company_name", "Employee Name": "employee_name", "Leave Start Date": "leave_start_date", "Leave End Date": "leave_end_date"}',
                'lang' => [
                    'ar' => '<p><strong>موضوع</strong>:- {employee_name} ترك الطلب المستلم</p>
                    <p>عزيزي {company_name},</p>
                    <p>أود أن أبلغكم أنني قدمت طلب إجازة من {leave_start_date} ل {leave_end_date}.</p>
                    <p>شكرًا لك</p>
                    <p><strong>يعتبر,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'da' => '<p><strong>Emne</strong>:- {employee_name} Efterlad ansøgning modtaget</p>
                    <p>Kære {company_name},</p>
                    <p>Jeg vil gerne meddele, at jeg har indsendt orlovsanmodning fra {leave_start_date} til {leave_end_date}.</p>
                    <p>tak skal du have</p>
                    <p><strong>Med venlig hilsen,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'de' => '<p><strong>Thema</strong>:- {employee_name} Antrag eingegangen lassen</p>
                    <p>Liebling {company_name},</p>
                    <p>Ich möchte Ihnen mitteilen, dass ich einen Urlaubsantrag gestellt habe {leave_start_date} Zu {leave_end_date}.</p>
                    <p>Danke</p>
                    <p><strong>Grüße,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'en' => '<p><strong>Subject</strong>:- {employee_name} Leave Application Received</p>
                    <p>Dear {company_name},</p>
                    <p>I would like to inform you that I have submitted leave request from {leave_start_date} to {leave_end_date}.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'es' => '<p><strong>Sujeto</strong>:- {employee_name} Dejar solicitud recibida</p>
                    <p>Estimado {company_name},</p>
                    <p>Me gustaría informarles que he enviado una solicitud de permiso de {leave_start_date} a {leave_end_date}.</p>
                    <p>Gracias</p>
                    <p><strong>Saludos,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'fr' => '<p><strong>Sujet</strong>:- {employee_name} Demande de congé reçue</p>
                    <p>Cher {company_name},</p>
                    <p>Je tiens à vous informer que jai soumis une demande de congé de {leave_start_date} à {leave_end_date}.</p>
                    <p>Merci</p>
                    <p><strong>Salutations,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'it' => '<p><strong>Soggetto</strong>:- {employee_name} Lascia la domanda ricevuta</p>
                    <p>Caro {company_name},</p>
                    <p>Vorrei informarvi che ho presentato richiesta di ferie da {leave_start_date} A {leave_end_date}.</p>
                    <p>Grazie</p>
                    <p><strong>Saluti,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'ja' => '<p><strong>主題</strong>:- {employee_name} 申請書を受領したままにする</p>
                    <p>親愛なる {company_name},</p>
                    <p>から休暇申請を提出しましたのでお知らせいたします。 {leave_start_date} に {leave_end_date}.</p>
                    <p>ありがとう</p>
                    <p><strong>よろしく,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'nl' => '<p><strong>Onderwerp</strong>:- {employee_name} Laat de aanvraag ontvangen achter</p>
                    <p>Beste {company_name},</p>
                    <p>Hierbij wil ik u laten weten dat ik een verlofaanvraag heb ingediend bij {leave_start_date} naar {leave_end_date}.</p>
                    <p>Bedankt</p>
                    <p><strong>Groeten,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'pl' => '<p><strong>Temat</strong>:- {employee_name} Zostaw otrzymany wniosek</p>
                    <p>Droga {company_name},</p>
                    <p>Uprzejmie informuję, że złożyłem wniosek urlopowy z dn {leave_start_date} Do {leave_end_date}.</p>
                    <p>Dziękuję</p>
                    <p><strong>Pozdrowienia,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'ru' => '<p><strong>Предмет</strong>:- {employee_name} Оставить заявку полученной</p>
                    <p>Дорогой {company_name},</p>
                    <p>Я хотел бы сообщить вам, что я подал заявление на отпуск от {leave_start_date} к {leave_end_date}.</p>
                    <p>Спасибо</p>
                    <p><strong>С уважением,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                    'pt' => '<p><strong>Assunto</strong>:- {employee_name} Deixar inscrição recebida</p>
                    <p>Querido {company_name},</p>
                    <p>Gostaria de informar que enviei pedido de licença de {leave_start_date} para {leave_end_date}.</p>
                    <p>Obrigado</p>
                    <p><strong>Cumprimentos,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                ],
            ],
            'Employee Leave Cancelled' => [
                'subject' => 'Employee Leave Cancelled',
                'variables' => '{ "App Url": "app_url", "App Name": "app_name", "Company Name": "company_name", "Employee Name": "employee_name", "Leave Start Date": "leave_start_date", "Leave End Date": "leave_end_date", "Leave Status": "leave_status"}',
                'lang' => [
                    'en' => '<p><strong>Subject</strong>:- {employee_name} Leave Cancellation</p>
                    <p>Dear {company_name},</p>
                    <p>I would like to inform you that I have cancelled my leave request from {leave_start_date} to {leave_end_date}.</p>
                    <p>The leave was previously {leave_status}.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>{employee_name}</strong></p>',
                ],
            ],
            'Leave Request Approved' => [
                'subject' => 'Leave Request Approved',
                'variables' => '{"App Name": "app_name","App Url": "app_url","Employee Name": "employee_name","Leave Reason": "leave_reason","Leave Start Date": "leave_start_date","Leave End Date": "leave_end_date","Total Leave Days": "total_leave_days","Remark": "remark"}',
                'lang' => [
                    'en' => '<p><strong>Subject</strong>:- Leave Request Approved</p>
                    <p>Dear {employee_name},</p>
                    <p>We are pleased to inform you that your leave request has been <strong>approved</strong>.</p>
                    <p><strong>Leave Details:</strong></p>
                    <p><strong>Reason:</strong> {leave_reason}<br>
                    <strong>Start Date:</strong> {leave_start_date}<br>
                    <strong>End Date:</strong> {leave_end_date}<br>
                    <strong>Total Days:</strong> {total_leave_days}</p>
                    <p>We request you to complete all your pending work or any other important tasks so that the company does not face any loss or problem during your absence. We appreciate your thoughtfulness to inform us well in advance.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'Leave Request Rejected' => [
                'subject' => 'Leave Request Rejected',
                'variables' => '{"App Name": "app_name","App Url": "app_url","Employee Name": "employee_name","Leave Reason": "leave_reason","Leave Start Date": "leave_start_date","Leave End Date": "leave_end_date","Total Leave Days": "total_leave_days","Remark": "remark"}',
                'lang' => [
                    'en' => '<p><strong>Subject</strong>:- Leave Request Rejected</p>
                    <p>Dear {employee_name},</p>
                    <p>We regret to inform you that your leave request has been <strong>rejected</strong>.</p>
                    <p><strong>Leave Details:</strong></p>
                    <p><strong>Reason:</strong> {leave_reason}<br>
                    <strong>Start Date:</strong> {leave_start_date}<br>
                    <strong>End Date:</strong> {leave_end_date}<br>
                    <strong>Total Days:</strong> {total_leave_days}</p>
                    <p><strong>Rejection Reason:</strong> {rejection_reason}</p>
                    <p>If you have any questions or concerns regarding this decision, please feel free to reach out to the HR department.</p>
                    <p>Thank you for your understanding.</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'New Helpdesk Ticket Reply' => [
                'subject' => 'New Reply on Helpdesk Ticket #{ticket_id}',
                'variables' => '{"App Name": "app_name", "App Url": "app_url", "Ticket Name": "ticket_name", "Ticket ID": "ticket_id", "Email": "email", "Reply Description": "reply_description"}',
                'lang' => [
                    'ar' => '<p><strong>الموضوع</strong>:- رد جديد على تذكرة الدعم #{ticket_id}</p>
                    <p>مرحباً،</p>
                    <p>تمت إضافة رد جديد على تذكرة الدعم الخاصة بك.</p>
                    <p><strong>تفاصيل التذكرة:</strong></p>
                    <p><strong>رقم التذكرة:</strong> #{ticket_id}<br>
                    <strong>اسم التذكرة:</strong> {ticket_name}</p>
                    <p><strong>الرد:</strong></p>
                    <p>{reply_description}</p>
                    <p>يرجى تسجيل الدخول لعرض المحادثة الكاملة والرد إذا لزم الأمر.</p>
                    <p>شكراً لك</p>
                    <p><strong>مع التحية،</strong></p>
                    <p><strong>فريق الدعم،</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'da' => '<p><strong>Emne</strong>:- Nyt svar på helpdesk-billet #{ticket_id}</p>
                    <p>Hej,</p>
                    <p>Et nyt svar er blevet tilføjet til din helpdesk-billet.</p>
                    <p><strong>Billet detaljer:</strong></p>
                    <p><strong>Billet-ID:</strong> #{ticket_id}<br>
                    <strong>Billet navn:</strong> {ticket_name}</p>
                    <p><strong>Svar:</strong></p>
                    <p>{reply_description}</p>
                    <p>Log venligst ind for at se den fulde samtale og svare om nødvendigt.</p>
                    <p>Tak</p>
                    <p><strong>Med venlig hilsen,</strong></p>
                    <p><strong>Supportteamet,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'de' => '<p><strong>Betreff</strong>:- Neue Antwort auf Helpdesk-Ticket #{ticket_id}</p>
                    <p>Hallo,</p>
                    <p>Eine neue Antwort wurde zu Ihrem Helpdesk-Ticket hinzugefügt.</p>
                    <p><strong>Ticket-Details:</strong></p>
                    <p><strong>Ticket-ID:</strong> #{ticket_id}<br>
                    <strong>Ticket-Name:</strong> {ticket_name}</p>
                    <p><strong>Antwort:</strong></p>
                    <p>{reply_description}</p>
                    <p>Bitte melden Sie sich an, um die vollständige Konversation anzuzeigen und bei Bedarf zu antworten.</p>
                    <p>Vielen Dank</p>
                    <p><strong>Mit freundlichen Grüßen,</strong></p>
                    <p><strong>Support-Team,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'en' => '<p><strong>Subject</strong>:- New Reply on Helpdesk Ticket #{ticket_id}</p>
                    <p>Hello,</p>
                    <p>A new reply has been added to your helpdesk ticket.</p>
                    <p><strong>Ticket Details:</strong></p>
                    <p><strong>Ticket ID:</strong> #{ticket_id}<br>
                    <strong>Ticket Name:</strong> {ticket_name}</p>
                    <p><strong>Reply:</strong></p>
                    <p>{reply_description}</p>
                    <p>Please log in to view the full conversation and respond if needed.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>Support Team,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'es' => '<p><strong>Asunto</strong>:- Nueva respuesta en ticket de soporte #{ticket_id}</p>
                    <p>Hola,</p>
                    <p>Se ha añadido una nueva respuesta a su ticket de soporte.</p>
                    <p><strong>Detalles del ticket:</strong></p>
                    <p><strong>ID del ticket:</strong> #{ticket_id}<br>
                    <strong>Nombre del ticket:</strong> {ticket_name}</p>
                    <p><strong>Respuesta:</strong></p>
                    <p>{reply_description}</p>
                    <p>Inicie sesión para ver la conversación completa y responder si es necesario.</p>
                    <p>Gracias</p>
                    <p><strong>Saludos,</strong></p>
                    <p><strong>Equipo de Soporte,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'fr' => '<p><strong>Objet</strong>:- Nouvelle réponse sur le ticket d\'assistance #{ticket_id}</p>
                    <p>Bonjour,</p>
                    <p>Une nouvelle réponse a été ajoutée à votre ticket d\'assistance.</p>
                    <p><strong>Détails du ticket :</strong></p>
                    <p><strong>ID du ticket :</strong> #{ticket_id}<br>
                    <strong>Nom du ticket :</strong> {ticket_name}</p>
                    <p><strong>Réponse :</strong></p>
                    <p>{reply_description}</p>
                    <p>Veuillez vous connecter pour voir la conversation complète et répondre si nécessaire.</p>
                    <p>Merci</p>
                    <p><strong>Cordialement,</strong></p>
                    <p><strong>Équipe de Support,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'it' => '<p><strong>Oggetto</strong>:- Nuova risposta sul ticket di assistenza #{ticket_id}</p>
                    <p>Ciao,</p>
                    <p>Una nuova risposta è stata aggiunta al tuo ticket di assistenza.</p>
                    <p><strong>Dettagli del ticket:</strong></p>
                    <p><strong>ID del ticket:</strong> #{ticket_id}<br>
                    <strong>Nome del ticket:</strong> {ticket_name}</p>
                    <p><strong>Risposta:</strong></p>
                    <p>{reply_description}</p>
                    <p>Accedi per visualizzare la conversazione completa e rispondere se necessario.</p>
                    <p>Grazie</p>
                    <p><strong>Cordiali saluti,</strong></p>
                    <p><strong>Team di Supporto,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'ja' => '<p><strong>件名</strong>:- ヘルプデスクチケット #{ticket_id} への新しい返信</p>
                    <p>こんにちは、</p>
                    <p>ヘルプデスクチケットに新しい返信が追加されました。</p>
                    <p><strong>チケット詳細：</strong></p>
                    <p><strong>チケットID：</strong> #{ticket_id}<br>
                    <strong>チケット名：</strong> {ticket_name}</p>
                    <p><strong>返信：</strong></p>
                    <p>{reply_description}</p>
                    <p>ログインして会話の全文を表示し、必要に応じて返信してください。</p>
                    <p>ありがとうございます</p>
                    <p><strong>よろしくお願いいたします、</strong></p>
                    <p><strong>サポートチーム、</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'nl' => '<p><strong>Onderwerp</strong>:- Nieuw antwoord op helpdesk-ticket #{ticket_id}</p>
                    <p>Hallo,</p>
                    <p>Er is een nieuw antwoord toegevoegd aan uw helpdesk-ticket.</p>
                    <p><strong>Ticket details:</strong></p>
                    <p><strong>Ticket-ID:</strong> #{ticket_id}<br>
                    <strong>Ticket naam:</strong> {ticket_name}</p>
                    <p><strong>Antwoord:</strong></p>
                    <p>{reply_description}</p>
                    <p>Log in om het volledige gesprek te bekijken en indien nodig te reageren.</p>
                    <p>Bedankt</p>
                    <p><strong>Met vriendelijke groet,</strong></p>
                    <p><strong>Supportteam,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'pl' => '<p><strong>Temat</strong>:- Nowa odpowiedź na zgłoszenie helpdesk #{ticket_id}</p>
                    <p>Witaj,</p>
                    <p>Nowa odpowiedź została dodana do Twojego zgłoszenia helpdesk.</p>
                    <p><strong>Szczegóły zgłoszenia:</strong></p>
                    <p><strong>ID zgłoszenia:</strong> #{ticket_id}<br>
                    <strong>Nazwa zgłoszenia:</strong> {ticket_name}</p>
                    <p><strong>Odpowiedź:</strong></p>
                    <p>{reply_description}</p>
                    <p>Zaloguj się, aby wyświetlić pełną konwersację i odpowiedzieć w razie potrzeby.</p>
                    <p>Dziękujemy</p>
                    <p><strong>Z poważaniem,</strong></p>
                    <p><strong>Zespół Wsparcia,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'pt' => '<p><strong>Assunto</strong>:- Nova resposta no ticket de suporte #{ticket_id}</p>
                    <p>Olá,</p>
                    <p>Uma nova resposta foi adicionada ao seu ticket de suporte.</p>
                    <p><strong>Detalhes do ticket:</strong></p>
                    <p><strong>ID do ticket:</strong> #{ticket_id}<br>
                    <strong>Nome do ticket:</strong> {ticket_name}</p>
                    <p><strong>Resposta:</strong></p>
                    <p>{reply_description}</p>
                    <p>Faça login para ver a conversa completa e responder se necessário.</p>
                    <p>Obrigado</p>
                    <p><strong>Atenciosamente,</strong></p>
                    <p><strong>Equipe de Suporte,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'ru' => '<p><strong>Тема</strong>:- Новый ответ на тикет службы поддержки #{ticket_id}</p>
                    <p>Здравствуйте,</p>
                    <p>К вашему тикету службы поддержки был добавлен новый ответ.</p>
                    <p><strong>Детали тикета:</strong></p>
                    <p><strong>ID тикета:</strong> #{ticket_id}<br>
                    <strong>Название тикета:</strong> {ticket_name}</p>
                    <p><strong>Ответ:</strong></p>
                    <p>{reply_description}</p>
                    <p>Пожалуйста, войдите в систему, чтобы просмотреть полную переписку и ответить при необходимости.</p>
                    <p>Спасибо</p>
                    <p><strong>С уважением,</strong></p>
                    <p><strong>Команда поддержки,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
        ];
        foreach ($emailTemplate as $eTemp) {
            $emailtemplate = EmailTemplate::updateOrCreate(
                [
                    'name' => $eTemp,
                ],
                [
                    'from' => 'RC ClearPay',
                    'created_by' => 1,
                    'workspace_id' => 0,
                ]
            );

            foreach ($defaultTemplate[$eTemp]['lang'] as $lang => $content) {
                EmailTemplateLang::updateOrCreate(
                    [
                        'parent_id' => $emailtemplate->id,
                        'lang' => $lang,
                    ],
                    [
                        'subject' => $defaultTemplate[$eTemp]['subject'],
                        'variables' => $defaultTemplate[$eTemp]['variables'],
                        'content' => $content,
                    ]
                );
            }
        }
    }
}
