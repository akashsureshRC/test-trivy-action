<?php

namespace App\Models;

use App\Mail\CommonEmailTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'from',
        'created_by',
        'workspace_id'
    ];

    public function template()
    {
        return $this->hasOne('App\Models\UserEmailTemplate', 'template_id', 'id')->where('user_id', '=', \Auth::user()->id);
    }

    public static function sendEmailTemplate($emailTemplate, $mailTo, $obj, $user_id = null, $workspace_id = null)
    {
        if (!empty($user_id)) {
            $usr = User::where('id', $user_id)->first();
        } else {
            $usr = Auth::user();
        }

        // unset($mailTo[$usr->id]);
        //Remove Current Login user Email don't send mail to them

        $mailTo = array_values($mailTo);

        // Templates are always global (managed by Super Admin, workspace_id = 0).
        $template = EmailTemplate::where('name', $emailTemplate)->first();

        if (isset($template) && !empty($template)) {
            // Determine language: prefer recipient's language, fall back to sender's, then 'en'
            $recipientLang = $usr->lang ?? 'en';
            $firstRecipient = $mailTo[0] ?? null;
            if ($firstRecipient) {
                $recipientUser = User::where('email', $firstRecipient)->first();
                if ($recipientUser && !empty($recipientUser->lang)) {
                    $recipientLang = $recipientUser->lang;
                }
            }

            // get email content in recipient's language
            $content = EmailTemplateLang::where('parent_id', '=', $template->id)
                ->where('lang', 'LIKE', $recipientLang)->first();

            // Fall back to English if template not available in recipient's language
            if (empty($content)) {
                $content = EmailTemplateLang::where('parent_id', '=', $template->id)
                    ->where('lang', 'LIKE', 'en')->first();
            }

            $content->from = $template->from;
            if (!empty($content->content)) {
                $content->content = self::replaceVariable($content->content, $obj);

                $mailFromAddress = companySetting('mail_from_address', $user_id, $workspace_id);

                // send email
                if (!empty($mailFromAddress)) {
                    if (!empty($user_id) && empty($workspace_id)) {
                        $setconfing =  setConfigEmail($user_id);
                    } elseif (!empty($user_id) && !empty($workspace_id)) {
                        $setconfing =  setConfigEmail($user_id, $workspace_id);
                    } else {
                        $setconfing =  setConfigEmail();
                    }
                    if ($setconfing ==  true) {
                        try {
                            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $user_id, $workspace_id));
                        } catch (\Exception $e) {

                            $error = $e->getMessage();
                        }
                    } else {
                        $error = __('Something went wrong please try again ');
                    }
                } else {
                    $error = __('E-Mail has been not sent due to SMTP configuration');
                }

                if (isset($error)) {

                    $arReturn = [
                        'is_success' => false,
                        'error' => $error,
                    ];
                } else {
                    $arReturn = [
                        'is_success' => true,
                        'error' => false,
                    ];
                }
            } else {
                $arReturn = [
                    'is_success' => false,
                    'error' => __('Mail not send, email is empty'),
                ];
            }
            return $arReturn;
        } else {
            return [
                'is_success' => false,
                'error' => __('Mail not send, email not found'),
            ];
        }
        // }
    }
    public static function replaceVariable($content, $obj)
    {
        $arrVariable = [
            '{app_name}',
            '{app_url}',
            '{company_name}',

            '{email}',
            '{password}',

            '{leave_status_name}',
            '{leave_status}',
            '{leave_reason}',
            '{leave_start_date}',
            '{leave_end_date}',
            '{total_leave_days}',
            '{rejection_reason}',

            '{name}',
            '{payslip_email}',
            '{salary_month}',
            '{url}',

            '{ticket_name}',
            '{ticket_id}',
            '{reply_description}',
            '{ticket_url}',

            '{employee_name}',
        ];
        $arrValue    = [
            'app_name' => '-',
            'app_url' => '-',
            'company_name' => '-',
            'email' => '-',
            'password' => '-',

            'leave_status_name' => '-',
            'leave_status' => '-',
            'leave_reason' => '-',
            'leave_start_date' => '-',
            'leave_end_date' => '-',
            'total_leave_days' => '-',
            'rejection_reason' => '-',

            'name' => '-',
            'payslip_email' => '-',
            'salary_month' => '-',
            'url' => '-',

            'ticket_name' => '-',
            'ticket_id' => '-',
            'reply_description' => '-',
            'ticket_url' => '-',

            'employee_name' => '-',
        ];
        foreach ($obj as $key => $val) {

            $arrValue[$key] = $val;
        }
        $arrValue['app_name']     = env('APP_NAME');
        $arrValue['company_name'] = (isset($arrValue['company_name']) && !empty($arrValue['company_name'])) ? $arrValue['company_name'] : '--';
        $arrValue['app_url']      = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';
        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public static $email_settings = [
        "custom" => "Custom",
        "smtp" => "SMTP",
        "gmail" => "Gmail",
        "outlook" => "Outlook/Office 365",
        "yahoo" => "Yahoo",
        "sendgrid" => "SendGrid",
        "amazon" => "Amazon SES ",
        "mailgun" => "Mailgun",
        "smtp.com" => "SMTP.com",
        "zohomail" => "Zoho Mail",
        "mandrill" => "Mandrill",
        "mailtrap" => "Mailtrap",
        "sparkpost" => "SparkPost"
    ];
}
