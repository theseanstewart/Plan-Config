<?php

if ( ! function_exists('plan')) {


    /**
     * @param $key
     * @param string|array|object $plan
     * @return mixed
     */
    function plan($key, $plan = null)
    {
        $planConfig = app('planconfig');

        if ( ! is_null($key)) {
            return $planConfig->get($key, $plan);
        }

        return $planConfig;
    }

}