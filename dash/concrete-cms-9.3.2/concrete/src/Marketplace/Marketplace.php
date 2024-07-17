<?php

namespace Concrete\Core\Marketplace;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\File\Service\File;
use Concrete\Core\Http\Request;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Permission\Checker as TaskPermission;
use Concrete\Core\Site\InstallationService;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Url\Resolver\PathUrlResolver;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * @deprecated This will be removed in version 10
 * @see PackageRepositoryInterface
 */
class Marketplace implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public const E_INVALID_BASE_URL = 20;

    public const E_MARKETPLACE_SUPPORT_MANUALLY_DISABLED = 21;

    public const E_UNRECOGNIZED_SITE_TOKEN = 22;

    public const E_DELETED_SITE_TOKEN = 31;

    public const E_SITE_TYPE_MISMATCH_MULTISITE = 34;

    public const E_CONNECTION_TIMEOUT = 41;

    public const E_GENERAL_CONNECTION_ERROR = 99;

    protected $isConnected = false;

    /**
     * @var bool|int
     */
    protected $connectionError = false;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var Repository
     */
    protected $databaseConfig;

    /**
     * @var File
     */
    protected $fileHelper;

    /**
     * @var PathUrlResolver
     */
    protected $urlResolver;

    /**
     * @var Request
     */
    protected $request;

    public function setApplication(\Concrete\Core\Application\Application $application)
    {
        $this->app = $application;

        $this->fileHelper = $this->app->make('helper/file');
        $this->config = $this->app->make('config');
        $this->databaseConfig = $this->app->make('config/database');
        $this->urlResolver = $this->app->make(PathUrlResolver::class);
        $this->request = $this->app->make(Request::class);
        $this->isConnected = false;
        $this->isConnected();
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        if ($this->isConnected) {
            return true;
        }

        if (!$this->config->get('concrete.marketplace.enabled')) {
            $this->connectionError = self::E_MARKETPLACE_SUPPORT_MANUALLY_DISABLED;

            return false;
        }

        $csToken = $this->databaseConfig->get('concrete.marketplace.token');

        $this->isConnected = false;

        if ($csToken != '') {
            $ms = '';
            $installationService = $this->app->make(InstallationService::class);
            if ($installationService->isMultisiteEnabled()) {
                $ms = '&ms=1';
            }
            $csiURL = urlencode($this->getSiteURL());
            $url = $this->config->get('concrete.urls.concrete_secure') . $this->config->get('concrete.urls.paths.marketplace.connect_validate') . "?csToken={$csToken}&csiURL=" . $csiURL . '&csiVersion=' . APP_VERSION . $ms;
            $vn = $this->app->make('helper/validation/numbers');
            $r = $this->get($url);

            if ($r === null && !$this->connectionError) {
                $this->isConnected = true;
            } else {
                if ($vn->integer($r)) {
                    $this->isConnected = false;
                    $this->connectionError = $r;

                    if ($this->connectionError == self::E_DELETED_SITE_TOKEN) {
                        $this->databaseConfig->clear('concrete.marketplace.token');
                        $this->databaseConfig->clear('concrete.marketplace.url_token');
                    }
                } else {
                    $this->isConnected = false;
                    $this->connectionError = self::E_GENERAL_CONNECTION_ERROR;
                }
            }
        }

        return $this->isConnected;
    }

    /**
     * @return static|Marketplace
     */
    public static function getInstance(): self
    {
        static $instance;
        if (!isset($instance)) {
            $instance = Application::getFacadeApplication()->make(__CLASS__);
        }

        return $instance;
    }

    /**
     * @param $file
     *
     * @return int|mixed|string
     */
    public static function downloadRemoteFile($file)
    {
        // Get the marketplace instance
        $marketplace = static::getInstance();
        $file .= '?csiURL=' . urlencode($marketplace->getSiteURL()) . '&csiVersion=' . APP_VERSION;
        $timestamp = time();
        $tmpFile = $marketplace->fileHelper->getTemporaryDirectory() . '/' . $timestamp . '.zip';
        $error = $marketplace->app->make('error');

        $chunksize = 1 * (1024 * 1024); // split into 1MB chunks
        $handle = fopen($file, 'rb');
        $fp = fopen($tmpFile, 'w');

        if ($handle === false) {
            $error->add(t('An error occurred while downloading the package.'));
        } elseif ($fp === false) {
            $error->add(t('Concrete was not able to save the package.'));
        } else {
            while (!feof($handle)) {
                $data = fread($handle, $chunksize);
                $data = is_numeric($data) ? (int) $data : $data;

                if ($data === Package::E_PACKAGE_INVALID_APP_VERSION) {
                    $error->add(t('This package isn\'t currently available for this version of Concrete . Please contact the maintainer of this package for assistance.'));
                } else {
                    fwrite($fp, $data, strlen($data));
                }
            }

            fclose($handle);
            fclose($fp);
        }

        if ($error->has()) {
            if (file_exists($tmpFile)) {
                @unlink($tmpFile);
            }

            return $error;
        }

        return $timestamp;
    }

    /**
     * Runs through all packages on the marketplace, sees if they're installed here, and updates the available version number for them.
     */
    public static function checkPackageUpdates()
    {
        $marketplace = static::getInstance();
        $skipPackages = $marketplace->config->get('concrete.updates.skip_packages');
        if ($skipPackages === true) {
            return;
        }
        if (!$skipPackages) {
            // In case someone uses false or NULL or an empty string
            $skipPackages = [];
        } else {
            // In case someone uses a single package handle
            $skipPackages = (array) $skipPackages;
        }
        /** @var EntityManagerInterface $em */
        $em = $marketplace->app->make(EntityManagerInterface::class);
        /** @var PackageService $packageService */
        $packageService = $marketplace->app->make(PackageService::class);
        $items = self::getAvailableMarketplaceItems(false);
        foreach ($items as $i) {
            if (in_array($i->getHandle(), $skipPackages, true)) {
                continue;
            }
            $p = $packageService->getByHandle($i->getHandle());
            if (is_object($p)) {
                /**
                 * @var \Concrete\Core\Entity\Package $p
                 */
                $p->setPackageAvailableVersion($i->getVersion());
                $em->persist($p);
            }
        }
        $em->flush();
    }

    public static function getAvailableMarketplaceItems($filterInstalled = true): array
    {
        $marketplace = static::getInstance();
        /** @var PackageService $packageService */
        $packageService = $marketplace->app->make(PackageService::class);

        $fh = $marketplace->fileHelper;
        if (!$fh) {
            return [];
        }

        // Retrieve the URL contents
        $csToken = $marketplace->databaseConfig->get('concrete.marketplace.token');
        $csiURL = urlencode($marketplace->getSiteURL());
        $url = $marketplace->config->get('concrete.urls.concrete_secure') . $marketplace->config->get('concrete.urls.paths.marketplace.purchases');
        $url .= "?csToken={$csToken}&csiURL=" . $csiURL . '&csiVersion=' . APP_VERSION;
        $json = $marketplace->get($url);

        $addons = [];

        $objects = @$marketplace->app->make('helper/json')->decode($json);
        if (is_array($objects)) {
            try {
                foreach ($objects as $addon) {
                    $mi = new RemoteItem();
                    $mi->setPropertiesFromJSONObject($addon);
                    $remoteCID = $mi->getRemoteCollectionID();
                    if (!empty($remoteCID)) {
                        $addons[$mi->getHandle()] = $mi;
                    }
                }
            } catch (Exception $e) {
            }

            if ($filterInstalled) {
                $handles = $packageService->getInstalledHandles();
                if (is_array($handles)) {
                    $adlist = [];
                    foreach ($addons as $key => $ad) {
                        if (!in_array($ad->getHandle(), $handles)) {
                            $adlist[$key] = $ad;
                        }
                    }
                    $addons = $adlist;
                }
            }
        }

        return $addons;
    }

    public function getConnectionError()
    {
        return $this->connectionError;
    }

    public function getSitePageURL(): string
    {
        $token = $this->databaseConfig->get('concrete.marketplace.url_token');
        $url = $this->config->get('concrete.urls.concrete_secure') . $this->config->get('concrete.urls.paths.site_page');

        return $url . '/' . $token;
    }

    public function getMarketplaceFrame($width = '100%', $height = '300', $completeURL = false, $connectMethod = 'view'): string
    {
        // if $mpID is passed, we are going to either
        // a. go to its purchase page
        // b. pass you through to the page AFTER connecting.
        $tp = new TaskPermission();
        if ($this->request->getScheme() === 'https') {
            $frameURL = $this->config->get('concrete.urls.concrete_secure');
        } else {
            $frameURL = $this->config->get('concrete.urls.concrete');
        }
        if ($tp->canInstallPackages()) {
            $csToken = null;
            if (!$this->isConnected()) {
                if (!$completeURL) {
                    $completeURL = $this->urlResolver->resolve(['/dashboard/extend/connect', 'connect_complete']);
                    $completeURL = $completeURL->setQuery([
                        'ccm_token' => $this->app->make('token')->generate('marketplace/connect'),
                    ]);
                }
                $csReferrer = urlencode($completeURL);
                $csiURL = urlencode($this->getSiteURL());

                // this used to be the BASE_URL and not BASE_URL . DIR_REL but I don't have a method for that
                // and honestly I'm not sure why it needs to be that way
                $csiBaseURL = $csiURL;

                if ($this->hasConnectionError()) {
                    if ($this->connectionError == self::E_DELETED_SITE_TOKEN) {
                        $connectMethod = 'view';
                        $csToken = self::generateSiteToken();
                        if (!$csToken) {
                            return '<div class="ccm-error">' .
                                t('Unable to generate a marketplace token. Request timed out.') .
                                '</div>';
                        }
                    } else {
                        $csToken = $this->getSiteToken();
                    }
                } else {
                    // new connection
                    $csToken = self::generateSiteToken();
                    if (!$csToken) {
                        return '<div class="ccm-error">' .
                        t('Unable to generate a marketplace token. Request timed out.') .
                        '</div>';
                    }
                }

                $url = $frameURL . $this->config->get('concrete.urls.paths.marketplace.connect') . '/-/' . $connectMethod;
                $url = $url . '?ts=' . time() . '&csiBaseURL=' . $csiBaseURL . '&csiURL=' . $csiURL . '&csToken=' . $csToken . '&csReferrer=' . $csReferrer . '&csName=' . htmlspecialchars(
                    $this->app->make('site')->getSite()->getSiteName(),
                    ENT_QUOTES,
                    APP_CHARSET
                );
            } else {
                $csiBaseURL = urlencode($this->getSiteURL());
                $url = $frameURL . $this->config->get('concrete.urls.paths.marketplace.connect_success') . '?csToken=' . $this->getSiteToken() . '&csiBaseURL=' . $csiBaseURL;
            }

            if (!$csToken && !$this->isConnected()) {
                return '<div class="ccm-error">' . t(
                    'Unable to generate a marketplace token. Please ensure that allow_url_fopen is turned on, or that cURL is enabled on your server. If these are both true, It\'s possible your site\'s IP address may be denylisted for some reason on our server. Please ask your webhost what your site\'s outgoing cURL request IP address is, and email it to us at <a href="mailto:help@concretecms.com">help@concretecms.com</a>.'
                ) . '</div>';
            }
            $time = time();
            $ifr = '<script type="text/javascript">
                    window.addEventListener("message", function(e) {
                        jQuery.fn.dialog.hideLoader();
                        if (e.data == "loading") {
                            jQuery.fn.dialog.showLoader();
                        } else {
                            var eh = e.data;
                            eh = parseInt(eh) + 100;
                            $("#ccm-marketplace-frame-' . $time . '").attr("height", eh);
                        }
                        });
                    </script>';
            $ifr .= '<iframe class="ccm-marketplace-frame-connect" id="ccm-marketplace-frame-' . $time . '" frameborder="0" width="' . $width . '" height="' . $height . '" src="' . $url . '"></iframe>';

            return $ifr;
        }

        return '<div class="ccm-error">' . t(
            'You do not have permission to connect this site to the marketplace.'
        ) . '</div>';
    }

    public function hasConnectionError(): bool
    {
        return $this->connectionError != false;
    }

    public function generateSiteToken()
    {
        return $this->get(
            $this->config->get('concrete.urls.concrete_secure') .
            $this->config->get('concrete.urls.paths.marketplace.connect_new_token')
        );
    }

    public static function getSiteToken()
    {
        $marketplace = static::getInstance();

        $dbConfig = $marketplace->app->make('config/database');
        $token = $dbConfig->get('concrete.marketplace.token');

        return $token;
    }

    public function getSiteURL(): string
    {
        $url = $this->app->make('url/canonical');

        return rtrim((string) $url, '/');
    }

    public function getMarketplacePurchaseFrame($mp, $width = '100%', $height = '530'): string
    {
        $tp = new TaskPermission();
        if ($tp->canInstallPackages()) {
            if (!is_object($mp)) {
                return '<div class="alert-message block-message error">' . t(
                    'Unable to get information about this product.'
                ) . '</div>';
            }
            $url = '';
            if ($this->isConnected()) {
                $url = $this->config->get('concrete.urls.concrete_secure') . $this->config->get('concrete.urls.paths.marketplace.checkout');
                $csiURL = urlencode($this->getSiteURL());
                $csiBaseURL = $csiURL;
                $csToken = $this->getSiteToken();
                $url = $url . '/' . $mp->getProductBlockID() . '?ts=' . time() . '&csiBaseURL=' . $csiBaseURL . '&csiURL=' . $csiURL . '&csToken=' . $csToken;
            }

            $time = time();
            $ifr = '<script type="text/javascript">
                window.addEventListener("message", function(e) {
                    jQuery.fn.dialog.hideLoader();
                    if (e.data == "loading") {
                        jQuery.fn.dialog.showLoader();
                    } else {
                        var eh = e.data;
                        eh = parseInt(eh) + 100;
                        $("#ccm-marketplace-frame-' . $time . '").attr("height", eh);
                    }
                    });
                </script>';
            $ifr .= '<iframe class="ccm-marketplace-frame" id="ccm-marketplace-frame-' . $time . '" frameborder="0" width="' . $width . '" height="' . $height . '" src="' . $url . '"></iframe>';

            return $ifr;
        }

        return '<div class="ccm-error">' . t(
            'You do not have permission to connect this site to the marketplace.'
        ) . '</div>';
    }

    /**
     * Get the contents of a URL.
     *
     * @param $url
     *
     * @return string|null
     */
    private function get($url)
    {
        try {
            $result = $this->fileHelper->getContents(
                $url,
                $this->config->get('concrete.marketplace.request_timeout')
            );
        } catch (Exception $e) {
            $this->connectionError = self::E_GENERAL_CONNECTION_ERROR;

            return null;
        }

        if ($result === false) {
            $this->connectionError = self::E_GENERAL_CONNECTION_ERROR;
        }

        return $result ?: null;
    }
}
