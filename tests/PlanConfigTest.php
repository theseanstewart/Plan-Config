<?php

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Seanstewart\PlanConfig\Plan;
use Seanstewart\PlanConfig\PlanConfig;

class PlanConfigTest extends TestCase {

    protected $planConfig;
    protected $config;
    protected $auth;

    public function setUp()
    {
        parent::setUp();
        $this->config = new Config();
        $this->auth = new Auth();
        $this->planConfig = new Seanstewart\PlanConfig\PlanConfig($this->config, $this->auth);
    }

    protected function getPackageProviders($app)
    {
        return [\Seanstewart\PlanConfig\PlanConfigServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Plan' => Plan::class
        ];
    }

    /**
     * @test
     * @group function
     */
    public function it_tests_global_function_plan()
    {
        Plan::shouldReceive('get')
            ->once()
            ->with('key', 'plan')
            ->andReturn('foo!');

        $this->assertEquals('foo!', plan('key', 'plan'));

        Plan::shouldReceive('get')
            ->once()
            ->with('key', null)
            ->andReturn('foo!');

        $this->assertEquals('foo!', plan('key'));
    }

    /**
     * @test
     * @group function
     */
    public function it_tests_global_function_plan_no_key()
    {
        Plan::shouldReceive('get')->never();

        $this->assertInstanceOf(Seanstewart\PlanConfig\PlanConfig::class, plan(null));
    }

    /**
     * @test
     */
    public function it_tests_get_with_null_for_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['setContext', 'getCurrentUserPlan', 'getPlanKey'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
                   ->method('setContext')
                   ->with(null);

        $planConfig->expects($this->once())
                   ->method('getCurrentUserPlan')
                   ->willReturn('current_user_plan');

        $planConfig->expects($this->once())
                   ->method('getPlanKey')
                   ->with('key', 'current_user_plan')
                   ->willReturn(true);

        $this->assertTrue($planConfig->get('key'));
    }

    /**
     * @test
     */
    public function it_tests_get_with_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['setContext', 'getCurrentUserPlan', 'getPlanKey'], [$this->config, $this->auth]);

        $planConfig->expects($this->never())
                   ->method('setContext');

        $planConfig->expects($this->never())
                   ->method('getCurrentUserPlan');

        $planConfig->expects($this->once())
                   ->method('getPlanKey')
                   ->with('key', 'userPlan')
                   ->willReturn(true);

        $this->assertTrue($planConfig->get('key', 'userPlan'));
    }

    /**
     * @test
     */
    public function it_tests_get_with_user()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['setContext', 'getCurrentUserPlan', 'getPlanKey'], [$this->config, $this->auth]);

        $user = ['id' => 123];

        $planConfig->expects($this->once())
                   ->method('setContext')
                   ->with($user);

        $planConfig->expects($this->once())
                   ->method('getCurrentUserPlan')
                   ->willReturn('current_user_plan');

        $planConfig->expects($this->once())
                   ->method('getPlanKey')
                   ->with('key', 'current_user_plan')
                   ->willReturn(true);

        $this->assertTrue($planConfig->get('key', $user));
    }

    /**
     * @test
     */
    public function it_tests_get_all_plans()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getFallbackPlan'], [$this->config, $this->auth]);

        $plans = [
            'plan_1' => ['foo1' => 'bar1'],
            'plan_2' => ['foo2' => 'bar2'],
        ];

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('plans')
                   ->willReturn($plans);

        $this->assertEquals($plans, $planConfig->getAllPlans($plans));
    }

    /**
     * @test
     */
    public function it_gets_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getFallbackPlan'], [$this->config, $this->auth]);

        $fallbackPlan = ['bar' => 'foo'];

        $planGroup1 = [
            'plan_1' => ['foo1' => 'bar1'],
            'plan_2' => ['foo2' => 'bar2'],
        ];

        $planGroup2 = [
            'plan_3' => ['foo3' => 'bar3']
        ];

        $planConfig->expects($this->exactly(2))
                   ->method('getConfig')
                   ->withConsecutive(['plans', null], ['plans', null])
                   ->willReturnOnConsecutiveCalls($planGroup1, $planGroup2);

        $planConfig->expects($this->exactly(2))
            ->method('getFallbackPlan')
            ->willReturnOnConsecutiveCalls($fallbackPlan, $fallbackPlan);

        $this->assertEquals($planGroup1['plan_1'], $planConfig->getPlan('plan_1'));
        $this->assertEquals($fallbackPlan, $planConfig->getPlan('plan_2'));
    }

    /**
     * @test
     */
    public function it_gets_fallback_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getAllPlans'], [$this->config, $this->auth]);

        $plans = [
            'default' => ['bar' => 'foo'],
            'plan_1' => ['foo1' => 'bar1'],
            'plan_2' => ['foo2' => 'bar2']
        ];

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('fallback_plan')
                   ->willReturn('default');

        $planConfig->expects($this->once())
                   ->method('getAllPlans')
                   ->willReturn($plans);

        $this->assertEquals($plans['default'], $planConfig->getFallbackPlan());
    }

    /**
     * @test
     */
    public function it_gets_fallback_plan_no_fallback()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getAllPlans'], [$this->config, $this->auth]);

        $plans = [
            'plan_1' => ['foo1' => 'bar1'],
            'plan_2' => ['foo2' => 'bar2'],
        ];

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('fallback_plan')
                   ->willReturn('default');

        $planConfig->expects($this->once())
                   ->method('getAllPlans')
                   ->willReturn($plans);

        $this->assertEquals([], $planConfig->getFallbackPlan());
    }

    /**
     * @test
     */
    public function it_gets_config()
    {
        $config = [
            'plans' => [
                'foo' => [
                    'bar' => 'foobar'
                ]
            ]
        ];

        $this->config->shouldReceive('get')
                     ->with('plans')
                     ->andReturn($config['plans']);

        $this->assertEquals($config['plans'], $this->planConfig->getConfig());
        $this->assertEquals('foobar', $this->planConfig->getConfig('foo.bar'));
        $this->assertEquals(null, $this->planConfig->getConfig('foo.bar2'));
        $this->assertEquals('default', $this->planConfig->getConfig('foo.bar2', 'default'));
    }

    /**
     * @test
     */
    public function it_tests_get_plan_key()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getAllowedOverrides'], [$this->config, $this->auth]);

        $planConfig->expects($this->exactly(3))
                   ->method('getPlan')
                   ->with('plan')
                   ->willReturnOnConsecutiveCalls(
                       ['foo' => ['bar' => 'value']],
                       ['foo' => 'bar'],
                       ['foo' => ['bar' => 'value'], 'barfoo' => 'abc123']
                   );

        $planConfig->expects($this->exactly(3))
                   ->method('getAllowedOverrides')
                   ->willReturnOnConsecutiveCalls([], [], ['barfoo' => 'barfoobarfoo']);

        $this->assertEquals('value', $planConfig->getPlanKey('foo.bar', 'plan'));
        $this->assertNull($planConfig->getPlanKey('foo.bar', 'plan'));
        $this->assertEquals('barfoobarfoo', $planConfig->getPlanKey('barfoo', 'plan'));

    }

    /**
     * @test
     */
    public function it_tests_get_plan_key_with_overrides()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getAllowedOverrides'], [$this->config, $this->auth]);

        $planConfig->expects($this->exactly(2))
                   ->method('getPlan')
                   ->with('plan')
                   ->willReturnOnConsecutiveCalls(['foo' => ['bar' => 'value']], ['foo' => 'bar']);

        $planConfig->expects($this->exactly(2))
                   ->method('getAllowedOverrides')
                   ->willReturnOnConsecutiveCalls(['foo.bar' => 'override', 'extra.key' => 'foo'], []);

        $this->assertEquals('override', $planConfig->getPlanKey('foo.bar', 'plan'));
        $this->assertEquals('bar', $planConfig->getPlanKey('foo', 'plan'));
    }

    /**
     * @test
     */
    public function it_tests_extract_user_plan_overrides()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig'], [$this->config, $this->auth]);

        $overrides = ['foo.bar' => 'foo'];

        $user1 = [];
        $user2 = ['plan_overrides' => $overrides];

        $planConfig->expects($this->exactly(2))
                   ->method('getConfig')
                   ->with('overrides.user_model_attribute', null)
                   ->willReturn('plan_overrides');

        $this->assertEquals([], $planConfig->extractUserPlanOverrides($user1));
        $this->assertEquals($overrides, $planConfig->extractUserPlanOverrides($user2));
    }

    /**
     * @test
     */
    public function it_tests_get_allowed_overrides_no_overrides()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getCurrentUserPlanOverrides'], [$this->config, $this->auth]);

        $overrides = null;

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('overrides.allowed')
                   ->willReturn(null);

        $planConfig->expects($this->once())
                   ->method('getCurrentUserPlanOverrides')
                   ->willReturn([]);

        $this->assertEquals([], $planConfig->getAllowedOverrides('plan_overrides'));
    }

    /**
     * @test
     */
    public function it_tests_get_allowed_overrides_key_not_set()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getCurrentUserPlanOverrides'], [$this->config, $this->auth]);

        $overrides = ['foo' => 'bar'];

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('overrides.allowed')
                   ->willReturn(null);

        $planConfig->expects($this->once())
                   ->method('getCurrentUserPlanOverrides')
                   ->willReturn($overrides);

        $this->assertEquals([], $planConfig->getAllowedOverrides('plan_overrides'));
    }

    /**
     * @test
     */
    public function it_tests_get_allowed_overrides_allows_all_keys()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getCurrentUserPlanOverrides'], [$this->config, $this->auth]);

        $overrides = ['foo' => ['bar' => 'foobar']];

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('overrides.allowed')
                   ->willReturn(['*']);

        $planConfig->expects($this->once())
                   ->method('getCurrentUserPlanOverrides')
                   ->willReturn($overrides);

        $this->assertEquals(['foo.bar' => 'foobar'], $planConfig->getAllowedOverrides('plan_overrides'));
    }

    /**
     * @test
     */
    public function it_tests_get_allowed_overrides_returns_allowed_only()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getCurrentUserPlanOverrides'], [$this->config, $this->auth]);

        $overrides = [
            'foo'          => ['bar' => 'foobar'],
            'yes'          => ['foobar' => 'yeah'],
            'foobarfoobar' => 123,
            'nooverride'   => 'foo'
        ];

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('overrides.allowed')
                   ->willReturn(['foo.bar', 'yes.foobar', 'foobarfoobar']);

        $planConfig->expects($this->once())
                   ->method('getCurrentUserPlanOverrides')
                   ->willReturn($overrides);

        $this->assertEquals([
            'foo.bar'      => 'foobar',
            'yes.foobar'   => 'yeah',
            'foobarfoobar' => 123
        ], $planConfig->getAllowedOverrides('plan_overrides'));
    }

    /**
     * @test
     */
    public function it_tests_extract_user_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->with('plan_field')
                   ->willReturn('stripe_plan');

        $user = [
            'stripe_plan' => 'foobar'
        ];

        $this->assertEquals('foobar', $planConfig->extractUserPlan($user));
    }

    /**
     * @test
     */
    public function it_tests_get_authenticated_user()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan'], [$this->config, $this->auth]);

        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        $user = ['id' => 123];

        Auth::shouldReceive('getUser')
            ->once()
            ->andReturn($user);

        $this->assertEquals($user, $planConfig->getAuthenticatedUser());
    }

    /**
     * @test
     */
    public function it_tests_get_authenticated_user_not_logged_in()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan'], [$this->config, $this->auth]);

        Auth::shouldReceive('check')
            ->andReturn(false);

        Auth::shouldReceive('getUser')->never();

        $this->assertEquals([], $planConfig->getAuthenticatedUser());

    }

    /**
     * @test
     */
    public function it_tests_set_context()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getAuthenticatedUser', 'extractUserPlan', 'extractUserPlanOverrides'], [$this->config, $this->auth]);

        $user = ['id' => 1];

        $planConfig->expects($this->never())
                   ->method('getAuthenticatedUser');

        $planConfig->expects($this->once())
                   ->method('extractUserPlan')
                   ->with($user)
                   ->willReturn('plan');

        $planConfig->expects($this->once())
                   ->method('extractUserPlanOverrides')
                   ->with($user)
                   ->willReturn(['overrides']);

        $planConfig->setContext($user);

        $this->assertEquals('plan', $planConfig->getCurrentUserPlan());
        $this->assertEquals(['overrides'], $planConfig->getCurrentUserPlanOverrides());
    }
}