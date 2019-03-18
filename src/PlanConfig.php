<?php
/**
 * Created by PhpStorm.
 * User: seanstewart
 * Date: 8/9/15
 * Time: 7:58 AM
 */

namespace Seanstewart\PlanConfig;

use Illuminate\Support\Arr;
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

        // Merge the plan data with the overrides
        $planDot = array_merge($planDot, $this->getPlanOverrides());

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
     * @return array
     */
    public function getPlanOverrides()
    {
        $attribute = Config::get('plans.overrides.user_model_attribute');

        if(!$attribute)
        {
            return [];
        }

        return $this->getAllowedOverrides($attribute);
    }

    /**
     * @param $attribute
     * @return array
     */
    public function getAllowedOverrides($attribute)
    {
        $user = $this->getAuthenticatedUser();

        $keys = Config::get('plans.overrides.allowed');

        // Check if the property exists. If so, return it, otherwise set overrides to null
        $overrides = Arr::get($user, $attribute);

        // If we have no overrides, return an empty array
        if(!$overrides)
        {
            return [];
        }

        // If we have overrides, and we're allowing all, return the overrides
        if($keys == ['*'])
        {
            return Arr::dot($overrides);
        }

        // Only return the overrides that are allowed
        return Arr::only(Arr::dot($overrides), $keys);
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

        return Arr::get($this->getAuthenticatedUser(), $config['plan_field']);
    }

    /**
     * @return array
     */
    public function getAuthenticatedUser()
    {
        return Auth::check() ? Auth::getUser() : [];
    }

}