<?php
namespace Craft;


class Shortlist_Edition
{
    private $licenseName = 'shortlist.key';
    private $etConnectFailureKey = 'shortlistConnectFailure';
    private $etRecentPhoneHome = 'shortlistPhonedHome';
    private $requestProduct = 'shortlist';
    private $requestVersion = '1.1.0.dev1';
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $_endpoint;

    /**
     * @var int
     */
    private $_timeout;

    /**
     * @var EtModel
     */
    private $_model;

    /**
     * @var bool
     */
    private $_allowRedirects = true;

    /**
     * @var string
     */
    private $_userAgent;

    /**
     * @var string
     */
    private $_destinationFileName;

    // Public Methods
    // =========================================================================

    public function __construct($endpoint, $timeout = 30, $connectTimeout = 2)
    {
        $endpoint .= craft()->config->get('endpointSuffix');

        $this->_endpoint = $endpoint;
        $this->_timeout = $timeout;
        $this->_connectTimeout = $connectTimeout;

        $this->_model = new SearchPlus_EditionModel(array(
            'requestUrl'  => craft()->request->getHostInfo() . craft()->request->getUrl(),
            'requestIp'   => craft()->request->getIpAddress(),
            'requestTime' => DateTimeHelper::currentTimeStamp(),
            'requestPort' => craft()->request->getPort(),

            'craftBuild'   => CRAFT_BUILD,
            'craftVersion' => CRAFT_VERSION,
            'craftEdition' => craft()->getEdition(),
            'craftTrack'   => CRAFT_TRACK,
            'userEmail'    => craft()->userSession->getUser()->email,

            'licenseKey'     => $this->_getLicenseKey(),
            'requestProduct' => $this->requestProduct,
            'requestVersion' => $this->requestVersion,

        ));

        $this->_userAgent = 'Craft/' . craft()->getVersion() . '.' . craft()->getBuild();
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
     * @param $destinationFileName
     *
     * @return null
     */
    public function setDestinationFileName($destinationFileName)
    {
        $this->_destinationFileName = $destinationFileName;
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
        if (!craft()->cache->get($this->etRecentPhoneHome) || $force) {

            try {
                $missingLicenseKey = empty($this->_model->licenseKey);

                // No craft/config/license.key file and we can't write to the config folder. Don't even make the call home.
                if ($missingLicenseKey && !$this->_isConfigFolderWritable()) {
                    throw new EtException('Craft needs to be able to write to your “craft/config” folder and it can’t.', 10001);
                }

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

                        if ($this->_destinationFileName) {
                            $body = $response->getBody();

                            // Make sure we're at the beginning of the stream.
                            $body->rewind();

                            // Write it out to the file
                            IOHelper::writeToFile($this->_destinationFileName, $body->getStream(), true);

                            // Close the stream.
                            $body->close();

                            return IOHelper::getFileName($this->_destinationFileName);
                        }

                        $etModel = craft()->et->decodeEtModel($response->getBody());

                        if ($etModel) {
                            if ($missingLicenseKey && !empty($etModel->licenseKey)) {
                                $this->_setLicenseKey($etModel->licenseKey);
                            }

                            // Cache the license key status and which edition it has
                            craft()->cache->set('licenseKeyStatus', $etModel->licenseKeyStatus);
                            craft()->cache->set('licensedEdition', $etModel->licensedEdition);
                            craft()->cache->set('editionTestableDomain@' . craft()->request->getHostName(), $etModel->editionTestableDomain ? 1 : 0);

                            if ($etModel->licenseKeyStatus == LicenseKeyStatus::MismatchedDomain) {
                                craft()->cache->set('licensedDomain', $etModel->licensedDomain);
                            }

                            return $etModel;
                        } else {
                            Craft::log('Error in calling ' . $this->_endpoint . ' Response: ' . $response->getBody(), LogLevel::Warning);

                            if (craft()->cache->get($this->etConnectFailureKey)) {
                                // There was an error, but at least we connected.
                                craft()->cache->delete($this->etConnectFailureKey);
                            }
                        }
                    } else {
                        Craft::log('Error in calling ' . $this->_endpoint . ' Response: ' . $response->getBody(), LogLevel::Warning);

                        if (craft()->cache->get($this->etConnectFailureKey)) {
                            // There was an error, but at least we connected.
                            craft()->cache->delete($this->etConnectFailureKey);
                        }
                    }
                }
            } // Let's log and rethrow any EtExceptions.
            catch (EtException $e) {
                Craft::log('Error in ' . __METHOD__ . '. Message: ' . $e->getMessage(), LogLevel::Error);

                if (craft()->cache->get($this->etConnectFailureKey)) {
                    // There was an error, but at least we connected.
                    craft()->cache->delete($this->etConnectFailureKey);
                }

                throw $e;
            } catch (\Exception $e) {
                Craft::log('Error in ' . __METHOD__ . '. Message: ' . $e->getMessage(), LogLevel::Error);

                // Cache the failure for 5 minutes so we don't try again.
                craft()->cache->set($this->etConnectFailureKey, true, 300);
            }

        }

        return null;
    }

    /**
     * @return null|string
     */
    private function getLicenseKeyPath()
    {
        return craft()->path->getConfigPath() . $this->licenseName;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return null|string
     */
    private function _getLicenseKey()
    {
        $licenseKeyPath = $this->getLicenseKeyPath();

        if (($keyFile = IOHelper::fileExists($licenseKeyPath)) !== false) {
            return trim(preg_replace('/[\r\n]+/', '', IOHelper::getFileContents($keyFile)));
        }

        return null;
    }

    /**
     * @param $key
     *
     * @return bool
     * @throws Exception|EtException
     */
    private function _setLicenseKey($key)
    {
        // Make sure the key file does not exist first. Et will never overwrite a license key.
        if (($keyFile = IOHelper::fileExists($this->getLicenseKeyPath())) == false) {
            $keyFile = $this->getLicenseKeyPath();

            if ($this->_isConfigFolderWritable()) {
                preg_match_all("/.{50}/", $key, $matches);

                $formattedKey = '';
                foreach ($matches[0] as $segment) {
                    $formattedKey .= $segment . PHP_EOL;
                }

                return IOHelper::writeToFile($keyFile, $formattedKey);
            }

            throw new EtException('Craft needs to be able to write to your “craft/config” folder and it can’t.', 10001);
        }

        throw new Exception(Craft::t('Cannot overwrite an existing product license key file.'));
    }

    /**
     * @return bool
     */
    private function _isConfigFolderWritable()
    {
        return IOHelper::isWritable(IOHelper::getFolderName($this->getLicenseKeyPath()));
    }
}
