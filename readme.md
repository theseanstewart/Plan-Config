# Plan Config

This Laravel 5 package makes it easy to manage the rules/limits of your SaaS app subscription plans.

## How to install

Pull the package in through Composer.

```js
"require": {
    "seanstewart/plan-config": "dev-master"
}
```

Include the service provider within `app/config/app.php`.

```php
'providers' => [
    Seanstewart\PlanConfig\PlanConfigServiceProvider::class
];
```

Include the facade (optional) in `app/config/app.php`.

```php
'aliases' => [
    'Plan'       => Seanstewart\PlanConfig\Plan::class
];
```

Then you will need to generate your config by running the command

```js
php artisan vendor:publish
```

## How to Use

Let's say your app has subscription plans that limit the number of widgets a user can add. You would have some sort of logic that checks the number of widgets a user is allowed to have in their account. With Plan Config you can do that by calling the helper function plan().

```php
if($this->getCurrentNumberOfWidgets < plan('limits.widgets'))
{
    // Allow the user to add a new widget
}
```

The plan() helper function knows what plan the current user is subscribed to and grabs the limits you defined in your plans.php config file. You can use the helper function anywhere in your application (views, controllers, models, middleware, etc.). Using the previous example, your plan config file would look like this:

```php
'plans' => [

    'bronze'   => [
        'limits' => [
            'widgets' => 5
        ]
    ],

    'silver'   => [
        'limits' => [
            'widgets' => 10
        ]
    ],

    //...and so on

]
```

If your user is subscribed to the silver plan, they could only add 10 widgets. You can even adapt it to use other attributes, like a title, description, or pricing for your plans.

```php
'plans' => [

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
```

# Configuring Your Plans

To configure your plans, open up app/plans.php and start adding your plan details. By default the package assumes that you're using laravel's built in Auth, and that the user's plan is stored in the User model. You can set the field used to determine the user's plan in the config...

```php
'plan_field' => 'stripe_plan'
```

To configure your plans, add your plan data in the 'plans' array.

```php
'plans' => [

    'bronze'   => [
        'limits' => [
            'widgets' => 5
        ]
    ],

    'silver'   => [
        'limits' => [
            'widgets' => 10
        ]
    ],

    //...and so on

]
```

If you have rules that apply to all plans, you can define a default or fallback plan. In the config file, set your fallback plan...

```php
'fallback_plan' => '_default',
```

And then define the _default plan in your plans array.

```php
'plans' => [

    '_default' => [
        'limits' => [
            'purple_widgets' => 20
        ]
    ]

    'bronze'   => [
        'limits' => [
            'widgets' => 5
        ]
    ],

    'silver'   => [
        'limits' => [
            'widgets' => 10
        ]
    ],

]
```

In the above example, calling plan('limits.purple_widgets') will give you the value from the fallback plan.

Alternatively you can use the facade and call Plan::get('limits.purple_widgets')

### Overrides

Plan config data can be overridden on the user level by setting an override attribute in the config.

```
'overrides' => [

        // The user model attribute that stores the attributes that can be changed
        'user_model_attribute' => 'plan_overrides',

        // The keys that are allowed to be changed. Set to all by default (['*']).
        'allowed' => ['*'],

    ]

```

In the above example, you would create the *plan_overrides* attribute on the user model. This field should be casted as an array and should include the list of keys and values that should be overridden for the given user.

#### Example

**Plan Config with Overrides:**
```
'overrides' => [

        'user_model_attribute' => 'plan_overrides',

        'allowed' => ['limits.apples', 'limits.bananas'],

    ],


'plans' => [

    '_default' => [
        'limits' => [
            'purple_widgets' => 20
        ]
    ]

    'bronze'   => [
        'limits' => [
            'widgets' => 5,
            'apples' => 10,
            'bananas' => 15
        ]
    ],

    'silver'   => [
        'limits' => [
            'widgets' => 10,
            'apples' => 15,
            'bananas' => 20
        ],
    ],

]
```

**User Model** *plan_overrides* attribute:
```
['limits.widgets' => 1, 'limits.apples' => 50, 'limits.bananas' => 100]
```

Would result in the following...

|  Key | Call | Result | Overridden? |
| --- | --- | --- | --- |
| limits.widgets | `plan('limits.widgets');` | **10** | No |
| limits.apples | `plan('limits.apples');` | **50** | Yes |
| limits.bananas | `plan('limits.bananas');` | **100** | Yes |


# Why I created this

I've always found that managing subscriptions and plans for a SaaS app can be complicated. I felt like storing these values in a database isn't the best approach considering a lot of your values and limits will not change frequently. When building [Election Runner](https://electionrunner.com), a web application that allows schools & organizations to run elections, we needed something to accomplish exactly this. Hopefully others will find this as useful as we do!

