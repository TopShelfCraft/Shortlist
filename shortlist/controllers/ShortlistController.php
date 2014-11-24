<?php
namespace Craft;

class ShortlistController extends BaseController
{
	protected $allowAnonymous = true;

	public function init()
	{
		$this->plugin = craft()->plugins->getPlugin('shortlist');

		if (!$this->plugin)
		{
			throw new Exception('Couldn’t find the Shortlist plugin!');
		}
	}
/*
	public function actionSaveSite()
	{
		$this->requirePostRequest();

		$site = new NeptuneModel();

		// Shared attributes
		$site->id         = craft()->request->getPost('siteId');
		$site->name       = craft()->request->getPost('name');
		$site->state 	  = craft()->request->getPost('state');
		$site->userId 	  = craft()->request->getPost('userId');
		$site->key 	  	  = craft()->request->getPost('key');

		// Save it
		if (craft()->neptune->saveSite($site))
		{
			craft()->userSession->setNotice(Craft::t('Site saved.'));
			$this->redirectToPostedUrl($site);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save site.'));
		}

		// Send the feature type back to the template
		craft()->urlManager->setRouteVariables(array(
			'site' => $site
		));
	}


    public function actionSiteCreate()
    {
    	$this->requirePostRequest();
    	$neptuneSite = new NeptuneModel();
		$neptuneSite->name = craft()->request->getPost('name');
		$neptuneSite->state = 'pending';
		$neptuneSite->key = StringHelper::randomString(18);

		if($neptuneSite->validate())
		{
			if (craft()->neptune->handleCreate($neptuneSite))
			{
				// Do we have any included features?
				// If we do - redirect to that url instead of the
				// direct redirect url

				$features = craft()->neptune_feature->getFeaturesBySiteId($neptuneSite->id);
				if(empty($features))
				{
					$this->redirectToPostedUrl($neptuneSite);
				}


				$firstFeature = current($features);
				$url = '/neptune/'.$neptuneSite->id.'/edit/'.$firstFeature->id;

				$this->redirect($url);
			}
			else
			{
				if(!empty(craft()->neptune->errors))
				{
					foreach(craft()->neptune->errors as $error)
					{
						$neptuneSite->addError('general', $error);
					}
				}
				else
				{
					$neptuneSite->addError('general', 'There was a problem with creating the site');
				}
			}
		}
		else
		{
			$neptuneSite->addError('general', 'There was a problem with your details, please check the form and try again');
		}

		$errors = array();
		foreach($neptuneSite->getErrors() as $key => $errs)
		{
			foreach($errs as $error)
			{
				if($key != 'general')	$errors[] = $key . ' : ' . $error;
				else $errors[] = $error;
			}
		}

		craft()->urlManager->setRouteVariables(array(
			'neptuneSite' => $neptuneSite,
			'allErrors' => $errors
		));
    }


    public function actionCharge()
    {
    	$this->requirePostRequest();
		$siteId = craft()->request->getPost('siteId');
		$neptuneSite = craft()->neptune->getSiteByIdForUser($siteId);

		if(is_null($neptuneSite)) {
			// Nope. Not allowed for this user, or bad site id
			craft()->urlManager->setRouteVariables(array(
				'errorMessage' => 'Sorry, you\'re not allowed to perform this action'
			));
			return;
		}

		$this->charge = new ChargeModel();
		$this->_collectData($neptuneSite);

		if($this->charge->validate() && craft()->charge->handlePayment($this->charge))
		{
			// Mark things as paid, and also send out any emails at the same time.
			craft()->neptune->markPaid($neptuneSite, $this->charge);
			$this->redirectToPostedUrl($neptuneSite);
		}
		else
		{
			if(!empty(craft()->charge->errors))
			{
				foreach(craft()->charge->errors as $error)
				{
					$this->charge->addError('general', $error);
				}
			}
			else
			{
				$this->charge->addError('general', 'There was a problem with payment');
			}

			// Also remove any card details
			$this->charge->cardToken = null;
			$this->charge->cardLast4 = null;
			$this->charge->cardType = null;

			if(isset($this->charge->planAmount) AND is_numeric($this->charge->planAmount))
			{
				$this->charge->planAmount = $this->charge->planAmount / 100;
			}

		}

		$errors = array();
		foreach($this->charge->getErrors() as $key => $errs)
		{
			foreach($errs as $error)
			{
				if($key != 'general')	$errors[] = $key . ' : ' . $error;
				else $errors[] = $error;
			}
		}

		craft()->urlManager->setRouteVariables(array(
			'neptuneSite' => $neptuneSite,
			'charge' => $this->charge,
			'allErrors' => $errors
		));

    }

    public function actionSiteConfirm()
    {
    	$this->requirePostRequest();
		$siteId = craft()->request->getPost('id');
		$neptuneSite = craft()->neptune->getSiteByIdForUser($siteId);

		if(is_null($neptuneSite)) {
			// Nope. Not allowed for this user, or bad site id
			craft()->urlManager->setRouteVariables(array(
				'errorMessage' => 'Sorry, you\'re not allowed to perform this action'
			));
			return;
		}


		// Validate we're in a state ready to update to a 'ready' state
		if($neptuneSite->validateStateChange('ready'))
		{
			if (craft()->neptune->updateState($neptuneSite, 'ready'))
			{
				$this->redirectToPostedUrl($neptuneSite);
			}
			else
			{
				if(!empty(craft()->neptune->errors))
				{
					foreach(craft()->neptune->errors as $error)
					{
						$neptuneSite->addError('general', $error);
					}
				}
				else
				{
					$neptuneSite->addError('general', 'There was a problem updating your site details');
				}
			}
		}
		else
		{
			$neptuneSite->addError('general', 'There was a problem updating your site details');
		}

		$errors = array();
		foreach($neptuneSite->getErrors() as $key => $errs)
		{
			foreach($errs as $error)
			{
				if($key != 'general')	$errors[] = $key . ' : ' . $error;
				else $errors[] = $error;
			}
		}

		craft()->urlManager->setRouteVariables(array(
			'neptuneSite' => $neptuneSite,
			'allErrors' => $errors
		));
    }


	private function _collectData(NeptuneModel $site)
	{
		$this->charge->cardToken 			= craft()->request->getPost('cardToken');
		$this->charge->cardLast4 			= craft()->request->getPost('cardLast4');
		$this->charge->cardType 			= craft()->request->getPost('cardType');
        $this->charge->cardName     		= craft()->request->getPost('cardName');
        $this->charge->cardExpMonth     	= craft()->request->getPost('cardExpMonth');
        $this->charge->cardExpYear     		= craft()->request->getPost('cardExpYear');
        $this->charge->cardAddressLine1     = craft()->request->getPost('cardAddressLine1');
        $this->charge->cardAddressLine2     = craft()->request->getPost('cardAddressLine2');
        $this->charge->cardAddressCity      = craft()->request->getPost('cardAddressCity');
        $this->charge->cardAddressState     = craft()->request->getPost('cardAddressState');
        $this->charge->cardAddressZip       = craft()->request->getPost('cardAddressZip');
        $this->charge->cardAddressCountry   = craft()->request->getPost('cardAddressCountry');

        $user = craft()->userSession->getUser();
        $name = $user->firstName . ' '. $user->lastName;
        if(trim($name) == '') $name = $user->username;
        $this->charge->customerName 		= $name;
		$this->charge->customerEmail 		= $user->email;


		$this->charge->planAmount 			= $site->amountOutstanding;
		$this->charge->planCoupon			= craft()->request->getPost('planCoupon');
		$this->charge->description 			= craft()->request->getPost('description');
	}
/*


	public function actionCharge()
	{
		$this->requirePostRequest();

		$this->charge = new ChargeModel();

		$this->_collectData();

		$settings = $this->plugin->getSettings();

		if($this->charge->validate())
		{
			if (craft()->charge->handlePayment($this->charge)) {
				$this->redirectToPostedUrl($this->charge);
			} else {

				if(!empty(craft()->charge->errors)) {
					foreach(craft()->charge->errors as $error) {
						$this->charge->addError('general', $error);
					}
				} else {
					$this->charge->addError('general', 'There was a problem with payment');
				}

				// Also remove any card details
				$this->charge->cardToken = null;
				$this->charge->cardLast4 = null;
				$this->charge->cardType = null;

				if(isset($this->charge->planAmount) AND is_numeric($this->charge->planAmount)) {
					$this->charge->planAmount = $this->charge->planAmount / 100;
				}

			}
		}
		else
		{
			$this->charge->addError('general', 'There was a problem with your details, please check the form and try again');
		}

		$errors = array();
		foreach($this->charge->getErrors() as $key => $errs) {
			foreach($errs as $error)
			{
				if($key != 'general')	$errors[] = $key . ' : ' . $error;
				else $errors[] = $error;
			}
		}

		craft()->urlManager->setRouteVariables(array(
			'charge' => $this->charge,
			'allErrors' => $errors
		));
	}




	public function actionDetails()
	{
		craft()->userSession->requirePermission('accessPlugin-Charge');
		$this->requirePostRequest();

		$chargeId = craft()->request->getPost('chargeId');
		$notes = craft()->request->getPost('notes');

		$details = array('notes' => $notes);

		if(craft()->charge->updateChargeDetails($chargeId, $details))
		{
			craft()->userSession->setNotice(Craft::t('Details updated.'));
      	} else {
            craft()->userSession->setError(Craft::t('Couldn\'t update item details.'));
        }

    	$this->redirectToPostedUrl();
	}

*/

}
