<?php

return [

    /*
   |--------------------------------------------------------------------------
   | User Model Plan Field
   |--------------------------------------------------------------------------
   |
   | Define the field that is used on the User model to define the
   | plan that the user is subscribed to.
   */

    'plan_field' => 'stripe_plan',


    /*
   |--------------------------------------------------------------------------
   | Fallback Plan
   |--------------------------------------------------------------------------
   |
   | The fallback plan will be used if one of the requested attributes
   | is not found in the user's subscription plan. If you don't define a
   | default fallback plan, then set this to false.
   */

    'fallback_plan' => '_default',

    /*
	|--------------------------------------------------------------------------
	| Overrides
	|--------------------------------------------------------------------------
	|
	| Here you can specify which keys can have their values changed using a model attribute
	*/

    'overrides' => [

        // The user model attribute that stores the attributes that can be changed
        'user_model_attribute' => 'plan_overrides',

        // The keys that are allowed to be changed. Set to all by default (['*']).
        'allowed' => ['*'],

    ],

    /*
	|--------------------------------------------------------------------------
	| Subscription plans
	|--------------------------------------------------------------------------
	|
	| Here you may define all of the plans that you offer, along with the
    | limits for each plan. The default plan will be used if no plan matches
	| the one provided when calling the plan() helper function.
	*/

    'plans'      => [

        '_default' => [

        ],

        'bronze' => [
            'title'       => 'Bronze Plan',
            'description' => 'This is some description for a Bronze Plan',
            'price'       => '19.00',
            'currency'    => 'USD',
            'limits'      => [
                'widgets' => 5
            ]
        ],

        'silver' => [
            'title'       => 'Silver Plan',
            'description' => 'This is some description for the Silver Plan',
            'price'       => '29.00',
            'currency'    => 'USD',
            'limits'      => [
                'widgets' => 10
            ]
        ],

        //...and so on

    ]
];
