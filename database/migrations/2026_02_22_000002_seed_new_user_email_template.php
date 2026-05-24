<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the "New User" email template with all language translations.
     * This template is used when:
     *  - Global Admin creates a Master Admin (admin branding)
     *  - Company Admin creates a Payroll Officer (company branding)
     */
    public function up(): void
    {
        // Skip if already exists
        if (DB::table('email_templates')->where('name', 'New User')->exists()) {
            return;
        }

        $templateId = DB::table('email_templates')->insertGetId([
            'name'         => 'New User',
            'from'         => 'System',
            'created_by'   => 1,
            'workspace_id' => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $subject   = 'Welcome - Your Account Has Been Created';
        $variables = json_encode([
            'App Url'      => 'app_url',
            'App Name'     => 'app_name',
            'Company Name' => 'company_name',
            'Email'        => 'email',
            'Password'     => 'password',
        ]);

        $translations = [
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
        ];

        $rows = [];
        foreach ($translations as $lang => $content) {
            $rows[] = [
                'parent_id'  => $templateId,
                'lang'       => $lang,
                'subject'    => $subject,
                'variables'  => $variables,
                'content'    => $content,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('email_template_langs')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $template = DB::table('email_templates')->where('name', 'New User')->first();

        if ($template) {
            DB::table('email_template_langs')->where('parent_id', $template->id)->delete();
            DB::table('email_templates')->where('id', $template->id)->delete();
        }
    }
};
