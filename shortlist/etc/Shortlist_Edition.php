<?php
namespace Craft;

class Shortlist_Edition
{
    private $etConnectFailureKey = 'squarebitConnectFailure';
    private $etRecentPhoneHome = 'squarebitPhonedHome';
    private $requestProduct;
    private $requestVersion;
    private $requestLicenseKey;

    // Properties
    // =========================================================================

    private $_endpoint;
    private $_timeout = 30;
    private $_model;
    private $_allowRedirects = true;
    private $_userAgent;
    private $_connectTimeout = 2;

    // Public Methods
    // =========================================================================

    public function __construct($endpoint, $product, $productVersion, $licenseKey = '')
    {
        $timeout = 30;
        $connectTimeout = 2;
        $this->requestProduct = $product;
        $this->requestVersion = $productVersion;

        $endpoint .= craft()->config->get('endpointSuffix');
        $this->_endpoint = $endpoint;
        $userEmail = craft()->userSession->getUser() ? craft()->userSession->getUser()->email : '';


        $this->_model = new Shortlist_LicenseModel(array(
            'requestUrl'  => craft()->request->getHostInfo() . craft()->request->getUrl(),
            'requestIp'   => craft()->request->getIpAddress(),
            'requestTime' => DateTimeHelper::currentTimeStamp(),
            'requestPort' => craft()->request->getPort(),

            //'craftBuild'   => CRAFT_BUILD,
            'craftVersion' => craft()->getVersion(),
            //'craftEdition' => craft()->getEdition(),
            //'craftTrack'   => CRAFT_TRACK,
            'userEmail'    => $userEmail,

            'requestProduct' => $this->requestProduct,
            'requestVersion' => $this->requestVersion,
            'licenseKey'     => $licenseKey
        ));

        $this->_userAgent = 'Craft/' . craft()->getVersion();
    }

    /**
     * The maximum number of seconds to allow for an entire transfer to take place before timing out.  Set 0 to wait
     * indefinitely.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * The maximum number of seconds to wait while trying to connect. Set to 0 to wait indefinitely.
     *
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->_connectTimeout;
    }

    /**
     * Whether or not to follow redirects on the request.  Defaults to true.
     *
     * @param $allowRedirects
     *
     * @return null
     */
    public function setAllowRedirects($allowRedirects)
    {
        $this->_allowRedirects = $allowRedirects;
    }

    /**
     * @return bool
     */
    public function getAllowRedirects()
    {
        return $this->_allowRedirects;
    }

    /**
     * @return EtModel
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Sets custom data on the EtModel.
     *
     * @param $data
     *
     * @return null
     */
    public function setData($data)
    {
        $this->_model->data = $data;
    }

    /**
     * @param $handle
     */
    public function setHandle($handle)
    {
        $this->_model->handle = $handle;
    }

    /**
     * @throws EtException|\Exception
     * @return EtModel|null
     */
    public function phoneHome($force = false)
    {

        if ($force) {
            if (craft()->cache->get($this->etConnectFailureKey)) {
                craft()->cache->delete($this->etConnectFailureKey);
            }
            if (craft()->cache->get($this->etRecentPhoneHome)) {
                craft()->cache->delete($this->etRecentPhoneHome);
            }
        }

        try {

            if (!craft()->cache->get($this->etConnectFailureKey)) {
                $data = JsonHelper::encode($this->_model->getAttributes(null, true));

                $client = new \Guzzle\Http\Client();
                $client->setUserAgent($this->_userAgent, true);

                $options = array(
                    'timeout'         => $this->getTimeout(),
                    'connect_timeout' => $this->getConnectTimeout(),
                    'allow_redirects' => $this->getAllowRedirects(),
                );


                $request = $client->post($this->_endpoint, $options);
                $request->setBody($data, 'application/json');

                // Potentially long-running request, so close session to prevent session blocking on subsequent requests.
                craft()->session->close();

                $response = $request->send();

                if ($response->isSuccessful()) {

                    // Clear the connection failure cached item if it exists.
                    if (craft()->cache->get($this->etConnectFailureKey)) {
                        craft()->cache->delete($this->etConnectFailureKey);
                    }

                    // Clear the connection failure cached item if it exists.
                    craft()->cache->set($this->etRecentPhoneHome, true, 300);

                    $etModel = craft()->shortlist_license->decodeEtModel($response->getBody());

                    if ($etModel) {
                        return $etModel;
                    } else {

                        if (craft()->cache->get($this->etConnectFailureKey)) {
                            // There was an error, but at least we connected.
                            craft()->cache->delete($this->etConnectFailureKey);
                        }
                    }
                } else {

                    if (craft()->cache->get($this->etConnectFailureKey)) {
                        // There was an error, but at least we connected.
                        craft()->cache->delete($this->etConnectFailureKey);
                    }
                }
            }
        } // Let's log and rethrow any EtExceptions.
        catch (EtException $e) {
            if (craft()->cache->get($this->etConnectFailureKey)) {
                // There was an error, but at least we connected.
                craft()->cache->delete($this->etConnectFailureKey);
            }

            throw $e;
        } catch (\Exception $e) {
            // Cache the failure for 5 minutes so we don't try again.
            craft()->cache->set($this->etConnectFailureKey, true, 300);
        }


        return null;
    }


}
