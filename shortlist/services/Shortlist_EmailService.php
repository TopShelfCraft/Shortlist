<?php

namespace Craft;

class Shortlist_EmailService extends BaseApplicationComponent
{

    public function getAll()
    {
        $emailRecords = Shortlist_EmailRecord::model()->findAll();

        return Shortlist_EmailModel::populateModels($emailRecords);
    }


    public function getEmailById($id)
    {
        $emailModel = $this->_getEmailModelById($id);

        return $emailModel;
    }


    public function saveEmail(Shortlist_EmailModel $model)
    {
        if ($model->id) {
            $record = Shortlist_EmailRecord::model()->findById($model->id);

            if (!$record) {
                throw new Exception(Craft::t('No email exists with the ID “{id}”',
                    ['id' => $model->id]));
            }
        } else {
            $record = new Shortlist_EmailRecord();
        }

        $record->name = $model->name;
        $record->handle = $model->handle;
        $record->subject = $model->subject;
        $record->to = $model->to;
        $record->bcc = $model->bcc;
        $record->enabled = $model->enabled;
        $record->templatePath = $model->templatePath;

        $record->validate();
        $model->addErrors($record->getErrors());

        if (!$model->hasErrors()) {
            // Save it!
            $record->save(false);

            $model->id = $record->id;

            return true;
        } else {
            return false;
        }
    }


    /**
     * Validates an inbound email, applies or adds an error
     *
     */
    public function getEmailByHandle($handle)
    {
        $email = $this->_getEmailModelByHandle($handle);

        return $email;
    }

    /**
     * Gets a emails's record.
     *
     * @access private
     * @param int $emailId
     * @return Shortlist_EmailModel
     */
    private function _getEmailRecordById($emailId = null)
    {
        if ($emailId) {
            $emailRecord = Shortlist_EmailRecord::model()->findById($emailId);

            if (!$emailRecord) {
                $this->_noEmailExists($emailId);
            }
        } else {
            $emailRecord = new Shortlist_EmailRecord();
        }

        return $emailRecord;
    }


    /**
     * Gets a emails's model.
     *
     * @access private
     * @param int $emailId
     * @return Shortlist_EmailModel
     */
    private function _getEmailModelById($emailId = null)
    {
        $record = $this->_getEmailRecordById($emailId);

        $model = Shortlist_EmailModel::populateModel($record);

        return $model;
    }


    /**
     * Gets a emails's model.
     *
     * @access private
     * @param varchar $handle
     * @return Shortlist_EmailModel
     */
    private function _getEmailModelByHandle($handle = null)
    {
        if ($handle) {
            $emailRecord = Shortlist_EmailRecord::model()->findByAttributes(
                ['handle' => $handle]);

            if (!$emailRecord) {
                return false;
            }

            $model = Shortlist_EmailModel::populateModel($emailRecord);

            return $model;
        }

        return false;
    }


    /**
     * Throws a "No source exists" exception.
     *
     * @access private
     * @param int $emailId
     * @throws Exception
     */
    private function _noEmailExists($emailId)
    {
        throw new Exception(Craft::t('No email exists with the ID “{id}”', ['id' => $emailId]));
    }


    /**
     * Delete a email from the db
     *
     * @param  int $id
     * @return int The number of rows affected
     */
    public function deleteEmailById($id)
    {
        $emailRecord = $this->_getEmailRecordById($id);

        return $emailRecord->deleteByPk($id);
    }


    public function sendByHandle($handle, $params = [])
    {
        $emailModel = $this->_getEmailModelByHandle($handle);
        if($emailModel == false) return false;

        $emailModel->send($params);
    }

}
