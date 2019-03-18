<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Mockery as m;

class PlanConfigTest extends PHPUnit_Framework_TestCase {

    protected $planConfig;
    protected $config;
    protected $auth;

    public function setUp()
    {
        $this->config = new Config();
        $this->auth = new Auth();
        $this->planConfig = new Seanstewart\PlanConfig\PlanConfig($this->config, $this->auth);
    }

    /**
     * @test
     */
    public function it_tests_get_without_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlanKey', 'getUserPlan'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
            ->method('getUserPlan')
            ->willReturn('userPlan');

        $planConfig->expects($this->once())
            ->method('getPlanKey')
            ->with('key', 'userPlan')
            ->willReturn(true);

        $this->assertTrue($planConfig->get('key'));
    }

    /**
     * @test
     */
    public function it_tests_get_with_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlanKey', 'getUserPlan'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
                   ->method('getPlanKey')
                   ->with('key', 'userPlan')
                   ->willReturn(true);

        $this->assertTrue($planConfig->get('key', 'userPlan'));
    }

    /**
     * @test
     */
    public function it_gets_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getFallbackPlan'], [$this->config, $this->auth]);

        $planGroup1 = [
            'plan_1' => ['foo1' => 'bar1'],
            'plan_2' => ['foo2' => 'bar2'],
        ];

        $planGroup2 = [
            'plan_3' => ['foo3' => 'bar3'],
        ];

        $planConfig->expects($this->exactly(2))
                   ->method('getConfig')
                   ->willReturnOnConsecutiveCalls(
                       [
                           'plans' => $planGroup1
                       ],
                       [
                           'plans' => $planGroup2
                       ]
                   );

        $planConfig->expects($this->once())
                   ->method('getFallbackPlan')
                   ->willReturn('fallback');

        $this->assertEquals($planGroup1['plan_1'], $planConfig->getPlan('plan_1'));
        $this->assertEquals('fallback', $planConfig->getPlan('plan_2'));
    }

    /**
     * @test
     */
    public function it_gets_fallback_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
                   ->method('getPlan')
                   ->with('fallback_plan')
                   ->willReturn(['plan' => 'fallbackPlan']);

        $this->assertEquals(['plan' => 'fallbackPlan'], $planConfig->getFallbackPlan());
    }

    /**
     * @test
     */
    public function it_gets_config()
    {
        $this->config->shouldReceive('get')->with('plans')->once()->andReturn('plansplansplans');

        $this->assertEquals('plansplansplans', $this->planConfig->getConfig());
    }

    /**
     * @test
     */
    public function it_gets_user_plan()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig', 'getAuthenticatedUser'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->willReturn(['plan_field' => 'stripe_plan']);

        $planConfig->expects($this->once())
                   ->method('getAuthenticatedUser')
                   ->willReturn(['stripe_plan' => 'this_is_the_plan']);

        $this->assertEquals('this_is_the_plan', $planConfig->getUserPlan());
    }

    /**
     * @test
     */
    public function it_tests_get_plan_key()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getPlanOverrides'], [$this->config, $this->auth]);

        $planConfig->expects($this->exactly(2))
                   ->method('getPlan')
                   ->with('plan')
                   ->willReturnOnConsecutiveCalls(['foo' => ['bar' => 'value']], ['foo' => 'bar']);

        $planConfig->expects($this->exactly(2))
                   ->method('getPlanOverrides')
                   ->willReturn([]);

        $this->assertEquals('value', $planConfig->getPlanKey('foo.bar', 'plan'));
        $this->assertFalse($planConfig->getPlanKey('foo.bar', 'plan'));
    }

    /**
     * @test
     */
    public function it_tests_get_plan_key_with_overrides()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getPlanOverrides'], [$this->config, $this->auth]);

        $planConfig->expects($this->exactly(2))
                   ->method('getPlan')
                   ->with('plan')
                   ->willReturnOnConsecutiveCalls(['foo' => ['bar' => 'value']], ['foo' => 'bar']);

        $planConfig->expects($this->exactly(2))
                   ->method('getPlanOverrides')
                   ->willReturnOnConsecutiveCalls(['foo.bar' => 'override', 'extra.key' => 'foo'], []);

        $this->assertEquals('override', $planConfig->getPlanKey('foo.bar', 'plan'));
        $this->assertEquals('bar', $planConfig->getPlanKey('foo', 'plan'));
    }

    /**
     * @test
     */
    public function it_tests_get_plan_of_user()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getUserPlan'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
                   ->method('getUserPlan')
                   ->willReturn('userPlan');

        $planConfig->expects($this->once())
                   ->method('getPlan')
                   ->with('userPlan')
                   ->willReturn(true);

        $this->assertTrue($planConfig->getPlanOfUser());
    }

    /**
     * @test
     */
    public function it_tests_get_plan_overrides()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getAllowedOverrides'], [$this->config, $this->auth]);

        $overrides = ['foo.bar' => 'foo'];

        Config::shouldReceive('get')
              ->once()
              ->with('plans.overrides.user_model_attribute')
              ->andReturn(null);

        Config::shouldReceive('get')
              ->once()
              ->with('plans.overrides.user_model_attribute')
              ->andReturn('plan_overrides');

        $planConfig->expects($this->once())
                   ->method('getAllowedOverrides')
                   ->with('plan_overrides')
                   ->willReturn($overrides);

        $this->assertEquals([], $planConfig->getPlanOverrides());
        $this->assertEquals($overrides, $planConfig->getPlanOverrides());
    }

    /**
     * @test
     */
    public function it_tests_get_allowed_overrides_no_overrides()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getAuthenticatedUser'], [$this->config, $this->auth]);

        $overrides = null;

        Config::shouldReceive('get')
              ->once()
              ->with('plans.overrides.allowed')
              ->andReturn(null);

        $planConfig->expects($this->once())
            ->method('getAuthenticatedUser')
            ->willReturn([]);

        $this->assertEquals([], $planConfig->getAllowedOverrides('plan_overrides'));
    }

    /**
     * @test
     */
    public function it_tests_get_allowed_overrides_key_not_set()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getAuthenticatedUser'], [$this->config, $this->auth]);

        $overrides = null;

        Config::shouldReceive('get')
              ->once()
              ->with('plans.overrides.allowed')
              ->andReturn(null);

        $planConfig->expects($this->once())
                   ->method('getAuthenticatedUser')
                   ->willReturn(['foo' => $overrides]);

        $this->assertEquals([], $planConfig->getAllowedOverrides('plan_overrides'));
    }

    /**
     * @test
     */
    public function it_tests_get_allowed_overrides_allows_all_keys()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getAuthenticatedUser'], [$this->config, $this->auth]);

        $overrides = ['foo' => [ 'bar' => 'foobar' ]];

        Config::shouldReceive('get')
              ->once()
              ->with('plans.overrides.allowed')
              ->andReturn(['*']);

        $planConfig->expects($this->once())
                   ->method('getAuthenticatedUser')
                   ->willReturn(['plan_overrides' => $overrides]);

        $this->assertEquals(['foo.bar' => 'foobar'], $planConfig->getAllowedOverrides('plan_overrides'));
    }

    /**
     * @test
     * @group 123
     */
    public function it_tests_get_allowed_overrides_returns_allowed_only()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan', 'getAuthenticatedUser'], [$this->config, $this->auth]);

        $overrides = [
            'foo'        => ['bar' => 'foobar'],
            'yes'        => ['foobar' => 'yeah'],
            'foobarfoobar' => 123,
            'nooverride' => 'foo'
        ];

        Config::shouldReceive('get')
              ->once()
              ->with('plans.overrides.allowed')
              ->andReturn(['foo.bar', 'yes.foobar', 'foobarfoobar']);

        $planConfig->expects($this->once())
                   ->method('getAuthenticatedUser')
                   ->willReturn(['plan_overrides' => $overrides]);

        $this->assertEquals([
            'foo.bar'    => 'foobar',
            'yes.foobar' => 'yeah',
            'foobarfoobar' => 123
        ], $planConfig->getAllowedOverrides('plan_overrides'));
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
}