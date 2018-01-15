<?php
namespace linkprofit\ReferralFilter;

use Yii;
use yii\base\ActionFilter;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\CookieCollection;
use yii\web\Session;

/**
 * Class ReferralFilter
 * @package linkprofit\ReferralFilter
 */
class ReferralFilter extends ActionFilter
{
    public $sessionMarkers = [];
    public $cookiesMarkers = [];

    /**
     * @var int seconds, by default until the browser is closed
     */
    public $cookiesExpire = 0;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CookieCollection
     */
    protected $cookies;


    /**
     * Init session and cookies variables
     */
    public function init()
    {
        $this->session = Yii::$app->session;
        $this->cookies = Yii::$app->response->cookies;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->session->open();

        $isSession = $this->setSessionMarkers();
        $isCookies = $this->setCookiesMarkers();
        if ($isSession || $isCookies) {
            $this->redirect();
        }

        return parent::beforeAction($action);
    }

    /**
     * @return bool
     */
    protected function setSessionMarkers()
    {
        $sessionMarkers = $this->session->get('markers', []);
        $sessionGetMarkers = $this->receiveGetMarkers($this->sessionMarkers);
        $this->session->set('markers', ArrayHelper::merge($sessionMarkers, $sessionGetMarkers));

        return !empty($sessionGetMarkers);
    }

    /**
     * @return bool
     */
    protected function setCookiesMarkers()
    {
        $cookiesGetMarkers = $this->receiveGetMarkers($this->cookiesMarkers);
        $expire = $this->cookiesExpire == 0 ? $this->cookiesExpire : time() + $this->cookiesExpire;

        foreach ($cookiesGetMarkers as $name => $value) {
            $this->addCookieMarker($name, $value, $expire);
        }

        return !empty($cookiesGetMarkers);
    }

    /**
     * @param $name
     * @param $value
     * @param $expire
     */
    protected function addCookieMarker($name, $value, $expire)
    {
        $this->cookies->add(new \yii\web\Cookie([
            'name' => $name,
            'value' => $value,
            'expire' => $expire
        ]));
    }

    /**
     * @param $names array
     * @return array
     */
    protected function receiveGetMarkers($names)
    {
        $result = [];
        foreach ($names as $name) {
            $marker = Yii::$app->request->get($name, NULL);
            if ($marker !== NULL) {
                $result[$name] = $marker;
            }
        }

        return $result;
    }

    /**
     * Redirect to current url without referral params
     */
    protected function redirect()
    {
        $paramsToDelete = array_merge($this->sessionMarkers, $this->cookiesMarkers);
        foreach ($paramsToDelete as $key => $name) {
            $paramsToDelete[$name] = null;
            unset($paramsToDelete[$key]);
        }

        $url = Url::current($paramsToDelete);
        Yii::$app->response->redirect($url);
        Yii::$app->end();
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }
}