<?php
namespace Craft;

use Mockery\CountValidator\Exception;

class Shortlist_EmailModel extends BaseModel
{
    protected function defineAttributes()
    {
        return [
            'id'           => [AttributeType::Number],
            'name'         => [AttributeType::String, 'required' => true],
            'handle'       => [AttributeType::String, 'required' => true],
            'subject'      => [AttributeType::String, 'required' => true],
            'to'           => [AttributeType::String, 'required' => true],
            'bcc'          => [AttributeType::String],
            'templatePath' => [AttributeType::String, 'required' => true],
            'enabled'      => [AttributeType::Bool, 'required' => true, 'default' => true],
        ];
    }



    public function send($params = [])
    {
        if($this->enabled != true) return;

        //sending emails
        $renderVariables = $params;

        // Just in case this is being triggered from the CP
        $oldPath = craft()->path->getTemplatesPath();
        $newPath = craft()->path->getSiteTemplatesPath();
        craft()->path->setTemplatesPath($newPath);

        $craftEmail = new EmailModel();

        try {
            $craftEmail->toEmail = $to = craft()->templates->renderString($this->to, $renderVariables);
        }
        catch (\Exception $e) {
            $error = Craft::t('Email template parse error for email “{email}” in “To:”. Template error: “{message}”',
                ['email' => $this->name, 'message' => $e->getMessage()]);
            ShortlistPlugin::log($error, LogLevel::Error, true);
            return false;
        }

        // BCC:
        try {
            $bcc = craft()->templates->renderString($this->bcc, $renderVariables);
            $bcc = str_replace(';',',',$bcc);
            $bcc = explode(',',$bcc);
            $bccEmails = [];
            foreach ($bcc as $bccEmail)
            {
                $bccEmails[] = ['email' => $bccEmail];
            }
            $craftEmail->bcc = $bccEmails;
        }
        catch (\Exception $e)
        {
            $error = Craft::t('Email template parse error for email “{email}” in “BCC:”. Template error: “{message}”',
                ['email' => $this->name, 'message' => $e->getMessage()]);
            ShortlistPlugin::log($error, LogLevel::Error, true);
            return false;
        }

        // Subject:
        try
        {
            $craftEmail->subject = craft()->templates->renderString($this->subject, $renderVariables);
        }
        catch (\Exception $e)
        {
            $error = Craft::t('Email template parse error for email “{email}” in “Subject:”. Template error: “{message}”',
                ['email' => $this->name, 'message' => $e->getMessage()]);
            ShortlistPlugin::log($error, LogLevel::Error, true);
            return false;
        }

        // Email Body
        if (!craft()->templates->doesTemplateExist($this->templatePath))
        {
            $error = Craft::t('Email template does not exist at “{templatePath}” for email “{email}”.',
                ['templatePath' => $this->templatePath, 'email' => $this->name]);
            ShortlistPlugin::log($error, LogLevel::Error, true);
            return false;
        }
        else
        {
            try
            {
                $craftEmail->body = $craftEmail->htmlBody = craft()->templates->render($this->templatePath,
                    $renderVariables);
            }
            catch (\Exception $e)
            {
                $error = Craft::t('Email template parse error for email “{email}”. Template error: “{message}”',
                    ['email' => $this->name, 'message' => $e->getMessage()]);
                ShortlistPlugin::log($error, LogLevel::Error, true);
                return false;
            }
        }

        try {
            if (!craft()->email->sendEmail($craftEmail)) {
                $error = Craft::t('Email “{email}” could not be sent. Errors: {errors}',
                    ['errors' => implode(", ", $this->getAllErrors()), 'email' => $this->name]);

                ShortlistPlugin::log($error, LogLevel::Error, true);
            }
        }
        catch(\Exception $e) {
            $error = Craft::t('Send email exception “{email}”. PHPMailerException error: “{message}”',
                ['email' => $this->name, 'message' => $e->getMessage()]);
            ShortlistPlugin::log($error, LogLevel::Error, true);
            return false;
        }


        craft()->path->setTemplatesPath($oldPath);

        return true;
    }

}
