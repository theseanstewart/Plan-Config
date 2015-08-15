<?php
/**
 * Created by PhpStorm.
 * User: seanstewart
 * Date: 8/9/15
 * Time: 7:58 AM
 */

namespace Seanstewart\PlanConfig;


use Illuminate\Support\Facades\Auth;

class PlanConfig {

    public $config;

    public $fallbackPlan;

    protected $_prefix = 'plans';

    function __construct()
    {
        $this->config = array_dot(config('plans'));
        $this->fallbackPlan = $this->config['fallback_plan'];
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
            $plan = $this->getUserPlan();

        // If the key exists for this plan, return the value
        if (array_key_exists($this->_prefix . '.' . $plan . '.' . $key, $this->config))
            return $this->config[$this->_prefix . '.' . $plan . '.' . $key];

        // If a fallback plan is set
        if ($this->fallbackPlan)
        {
            // If the key doesn't exist for the given plan, return the default value
            if (array_key_exists($this->_prefix . '.' . $this->fallbackPlan . '.' . $key, $this->config))
                return $this->config[$this->_prefix . '.' . $this->fallbackPlan . '.' . $key];
        }

        // If there is no default value set, return false
        return false;
    }

    /**
     * Return the value of the plan field from User model
     * The plan is defined in the plans.php config
     *
     * @return mixed
     */
    public function getUserPlan()
    {
        return Auth::user()->{$this->config['plan_field']};
    }

}