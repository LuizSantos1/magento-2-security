<?php

/**
 * Created by PhpStorm.
 * User: nghia
 * Date: 14/03/2018
 * Time: 10:57
 */

namespace Mageplaza\Security\Block\Adminhtml;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Config\ConfigOptionsListConstants;


class Checklist extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'Mageplaza_Security::checklist/index.phtml';

    protected $_userFactory;

    protected $_helper;

    protected $_deploymentConfig;
    protected $_metadata;

    protected $commonNames = ['admin', 'root', 'test', 'magento'];

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Magento\User\Model\UserFactory $userFactory,
        \Mageplaza\Security\Helper\Data $helper,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        Context $context,
        array $data = [])
    {
        $this->_metadata = $metadata;
        $this->_helper = $helper;
        $this->_userFactory = $userFactory;
        $this->_deploymentConfig = $deploymentConfig;
        parent::__construct($context, $data);
    }

    public function checkAdminUserName()
    {
        $userCollection = $this->_userFactory->create()->getCollection();
        $unSercureNames = [];
        foreach ($userCollection as $user) {
            if (in_array($user->getUserName(), $this->commonNames)) {
                $unSercureNames[] = [
                    'username' => $user->getUserName(),
                    'user_id' => $user->getUserId()
                ];
            }
        }
        return $unSercureNames;
    }

    public function checkFrontendCaptcha()
    {
        $customerCaptcha = $this->_helper->getConfigValue('customer/captcha/enable');
        return $customerCaptcha;

    }

    public function checkBackendCaptcha()
    {

        $adminCaptcha = $this->_helper->getConfigValue('admin/captcha/enable');
        return $adminCaptcha;
    }

    public function checkLatestVersion()
    {
        $realeases = file_get_contents('https://raw.githubusercontent.com/mageplaza/magento-versions/master/releases/releases.json');
        $arr = json_decode($realeases);
//        list($a,$b)= $arr;
//        $lastestVerAll= array_slice($arr, 0, 1);
        foreach ($arr as $key=>$value){
            $lastestVerAll=$key;
            break;
        }
        $versionArr = [];
        foreach ($arr as $ver => $item) {

            list($major, $minor, $patch) = explode('.', $ver);
            if ($major == 2) {
                $versionArr[$major . '.' . $minor][] = $ver;
            }
        }
        $lastestVer=[];
        foreach ($versionArr as $ver=>$version){
            $lastestVer[$ver]= $versionArr[$ver][0];
        }
        $currentVersion=$this->_metadata->getVersion();
//        list($currentMajor,$currentMinor,$currentPatch)= explode('.', $currentVersion);

        return [
            'lastestVer'=>$lastestVer,
            'currentVersion'=>$currentVersion
        ];

    }

    public function getDatabasePrefix()
    {
        return (string)$this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX);
    }
}