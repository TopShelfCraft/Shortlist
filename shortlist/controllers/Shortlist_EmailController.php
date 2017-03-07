<?php
namespace Craft;

class Shortlist_EmailController extends BaseController
{
    public function actionAll(array $variables = [])
    {
        $variables['emails'] = craft()->shortlist_email->getAll();

        $this->renderTemplate('shortlist/settings/email/index', $variables);
    }


    public function actionDeleteEmail()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();
        craft()->userSession->requireAdmin();

        $id = craft()->request->getRequiredPost('id');
        $return = craft()->shortlist_email->deleteEmailById($id);

        return $this->returnJson(['success' => $return]);
    }


    public function actionEdit(array $variables = [])
    {
        craft()->userSession->requireAdmin();

        if (!isset($variables['email'])) {

            if (isset($variables['emailId'])) {
                $emailId = $variables['emailId'];
                $variables['email'] = craft()->shortlist_email->getEmailById($emailId);
            } else {
                // New email, load a blank object
                $variables['email'] = new Shortlist_EmailModel();
            }
        }

        $this->renderTemplate('shortlist/settings/email/_edit', $variables);
    }


    public function actionSave()
    {
        $this->requirePostRequest();

        $email = new Shortlist_EmailModel();

        // Shared attributes
        $email->id = craft()->request->getPost('emailId');
        $email->name = craft()->request->getPost('name');
        $email->handle = craft()->request->getPost('handle');
        $email->subject = craft()->request->getPost('subject');
        $email->to = craft()->request->getPost('to');
        $email->bcc = craft()->request->getPost('bcc');
        $email->enabled = craft()->request->getPost('enabled');
        $email->templatePath = craft()->request->getPost('templatePath');

        // Save it
        if (craft()->shortlist_email->saveEmail($email)) {
            craft()->userSession->setNotice(Craft::t('Email saved.'));
            $this->redirectToPostedUrl($email);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save email.'));
        }

        // Send the model back to the template
        craft()->urlManager->setRouteVariables(['email' => $email]);
    }


}
