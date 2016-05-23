<?php
/**
 * Created by PhpStorm.
 * User: seanstewart
 * Date: 8/9/15
 * Time: 7:58 AM
 */

namespace Seanstewart\PlanConfig;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class PlanConfig {

    public $config;

    public $fallbackPlan = 'fallback_plan';

    protected $_prefix = 'plans';

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * PlanConfig constructor.
     * @param Config $config
     * @param Auth $auth
     */
    function __construct(Config $config, Auth $auth)
    {
        $this->auth = $auth;
        $this->config = $config;
    }

    /**
     * Get the config key
     * @param $key
     * @param null $plan
     * @return bool
     */
    public function get($key, $plan = null)
    {
        // If no plan is provided, get the user's plan
        if (!$plan)
        {
            return $this->getPlanKey($key, $this->getUserPlan());
        }

        return $this->getPlanKey($key, $plan);
    }

    /**
     * @param $plan
     * @return mixed
     */
    public function getPlan($plan)
    {
        $plans = $this->getConfig()['plans'];

        if (array_key_exists($plan, $plans))
        {
            return $plans[$plan];
        }

        return $this->getFallbackPlan();
    }

    /**
     * @param $key
     * @param $plan
     * @return bool
     */
    public function getPlanKey($key, $plan)
    {
        $plan = $this->getPlan($plan);

        // Since the key should be sent as array_dot
        // we need to convert the plan array to array_dot
        $planDot = array_dot($plan);

        if (array_key_exists($key, $planDot))
        {
            return $planDot[$key];
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getFallbackPlan()
    {
        return $this->getPlan($this->fallbackPlan);
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return Config::get('plans');
    }

    /**
     * @return mixed
     */
    public function getPlanOfUser()
    {
        return $this->getPlan($this->getUserPlan());
    }

    /**
     * Return the value of the plan field from User model
     * The plan is defined in the plans.php config
     *
     * @return mixed
     */
    public function getUserPlan()
    {
        $config = $this->getConfig();

        return Auth::user()->{$config['plan_field']};
    }

}