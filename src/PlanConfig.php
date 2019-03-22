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

    /**
     * @var string
     */
    protected $fallbackPlan = [];

    /**
     * @var
     */
    protected $configPlanField;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var
     */
    protected $currentUserPlanOverrides = [];

    /**
     * @var
     */
    protected $currentUserPlan;

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
     * @param string|object|array $plan
     * @return bool
     */
    public function get($key, $plan = null)
    {
        // If a string is provided, then we want to look up the key for a given plan
        if (is_string($plan))
        {
            return $this->getPlanKey($key, $plan);
        }

        // If the provided plan is not a string, then we are looking up a plan for a specific user and not
        // the actual config for a specific plan. This allows us to factor in the user's overrides
        $this->setContext($plan);

        return $this->getPlanKey($key, $this->getCurrentUserPlan());
    }

    /**
     * Returns all plans
     * @return mixed
     */
    public function getAllPlans()
    {
        return $this->getConfig('plans');
    }

    /**
     * @param $plan
     * @return mixed
     */
    public function getPlan($plan)
    {
        $plans = $this->getConfig('plans');

        return Arr::get($plans, $plan, $this->getFallbackPlan());
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
        $planDot = Arr::dot($plan);

        // Merge the plan data with the overrides
        $planDot = array_merge($planDot, $this->getAllowedOverrides());

        return Arr::get($planDot, $key, null);
    }

    /**
     * Returns the default fallback plan
     * @return mixed
     */
    public function getFallbackPlan()
    {
        return Arr::get($this->getAllPlans(), $this->getConfig('fallback_plan'), []);
    }

    /**
     * Returns the config for this package.
     * If a key is provided, then the value of the key will be returned.
     * If a key is provided and it doesn't exist, then the default value will be returned.
     * will be returned.
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function getConfig($key = null, $default = null)
    {
        $config = Config::get('plans');

        return $key ? Arr::get($config, $key, $default) : $config;
    }

    /**
     * Returns the user's plan overrides with only the keys allowed from the config
     * @return array
     */
    public function getAllowedOverrides()
    {
        $allowed = $this->getConfig('overrides.allowed');

        $overrides = $this->getCurrentUserPlanOverrides();

        // If we are allowing all overrides, then return all
        if ($allowed == ['*'])
        {
            return Arr::dot($overrides);
        }

        // Only return the overrides that are allowed
        return Arr::only(Arr::dot($overrides), $allowed);
    }

    /**
     * Returns the user's plan overrides
     * @param $user
     * @return array
     */
    public function extractUserPlanOverrides($user)
    {
        $attribute = $this->getConfig('overrides.user_model_attribute');

        // If this value is null, return an empty array
        if (!$attribute)
        {
            return [];
        }

        // Get the overrides from the user. If none, return an empty array.
        return Arr::get($user, $attribute, []);
    }

    /**
     * Returns the user's plan
     * @param $user
     * @return string
     */
    public function extractUserPlan($user)
    {
        $modelPlanAttribute = $this->getConfig('plan_field');

        return Arr::get($user, $modelPlanAttribute);
    }

    /**
     * @return array
     */
    public function getAuthenticatedUser()
    {
        return Auth::check() ? Auth::getUser() : [];
    }

    /**
     * Sets the user context for looking up the plan
     * @param $user
     */
    public function setContext($user)
    {
        // If a user isn't provided, then we want to get the authenticated user
        if(!$user)
        {
            $user = $this->getAuthenticatedUser();
        }

        $this->currentUserPlan = $this->extractUserPlan($user);
        $this->currentUserPlanOverrides = $this->extractUserPlanOverrides($user);
    }

    /**
     * Provides the current user's plan
     * @return mixed
     */
    public function getCurrentUserPlan()
    {
        return $this->currentUserPlan;
    }

    /**
     * Provides the current user's overrides
     * @return array
     */
    public function getCurrentUserPlanOverrides()
    {
        return $this->currentUserPlanOverrides;
    }

}