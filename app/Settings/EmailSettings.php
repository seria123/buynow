<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public string $mailer;
    public ?string $host;
    public ?int $port;
    public ?string $username;
    public ?string $password;
    public ?string $encryption;
    public string $from_address;
    public string $from_name;
    
    // Mailgun specific
    public ?string $mailgun_domain;
    public ?string $mailgun_secret;
    
    // SES specific
    public ?string $ses_key;
    public ?string $ses_secret;
    public ?string $ses_region;

    public static function group(): string
    {
        return 'email';
    }

    public static function locks(): array
    {
        return [];
    }

    public static function fields(): array
    {
        return [
            'mailer' => [
                'type' => 'string',
                'label' => 'Mail Mailer',
                'description' => 'The mail driver to use (smtp, mailgun, ses, sendmail, log)',
            ],
            'host' => [
                'type' => 'string',
                'label' => 'SMTP Host',
                'description' => 'SMTP server hostname',
            ],
            'port' => [
                'type' => 'integer',
                'label' => 'SMTP Port',
                'description' => 'SMTP server port',
            ],
            'username' => [
                'type' => 'string',
                'label' => 'SMTP Username',
                'description' => 'SMTP authentication username',
            ],
            'password' => [
                'type' => 'string',
                'label' => 'SMTP Password',
                'description' => 'SMTP authentication password',
            ],
            'encryption' => [
                'type' => 'string',
                'label' => 'SMTP Encryption',
                'description' => 'Encryption protocol (tls, ssl)',
            ],
            'from_address' => [
                'type' => 'string',
                'label' => 'From Address',
                'description' => 'Default sender email address',
            ],
            'from_name' => [
                'type' => 'string',
                'label' => 'From Name',
                'description' => 'Default sender name',
            ],
            'mailgun_domain' => [
                'type' => 'string',
                'label' => 'Mailgun Domain',
                'description' => 'Mailgun domain for sending emails',
            ],
            'mailgun_secret' => [
                'type' => 'string',
                'label' => 'Mailgun Secret',
                'description' => 'Mailgun API secret key',
            ],
            'ses_key' => [
                'type' => 'string',
                'label' => 'SES Access Key',
                'description' => 'AWS SES access key',
            ],
            'ses_secret' => [
                'type' => 'string',
                'label' => 'SES Secret Key',
                'description' => 'AWS SES secret key',
            ],
            'ses_region' => [
                'type' => 'string',
                'label' => 'SES Region',
                'description' => 'AWS SES region',
            ],
        ];
    }

    public static function defaults(): array
    {
        return [
            'mailer' => 'smtp',
            'host' => 'smtp.mailgun.org',
            'port' => 587,
            'username' => null,
            'password' => null,
            'encryption' => 'tls',
            'from_address' => 'noreply@buynow.com',
            'from_name' => 'BuyNow',
            'mailgun_domain' => null,
            'mailgun_secret' => null,
            'ses_key' => null,
            'ses_secret' => null,
            'ses_region' => 'us-east-1',
        ];
    }
}
