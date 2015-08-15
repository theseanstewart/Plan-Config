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

If you have rules that apply to all plays, you can define a default or fallback plan. In the config file, set your fallback plan...

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

# Why I created this

I've always found that managing subscriptions and plans for a SaaS app can be complicated. I felt like storing these values in a database isn't the best approach considering a lot of your values and limits will not change frequently. When building [Election Runner](https://electionrunner.com), a web application that allows schools & organizations to run elections, we needed something to accomplish exactly this. Hopefully others will find this as useful as we do!

