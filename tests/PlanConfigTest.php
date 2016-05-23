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
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getConfig'], [$this->config, $this->auth]);

        $planConfig->expects($this->once())
                   ->method('getConfig')
                   ->willReturn(['plan_field' => 'stripe_plan']);

        $this->auth->shouldReceive('user')
                   ->once()
                   ->andReturn((object) ['stripe_plan' => 'this_is_the_plan']);

        $this->assertEquals('this_is_the_plan', $planConfig->getUserPlan());
    }

    /**
     * @test
     */
    public function it_tests_get_plan_key()
    {
        $planConfig = $this->getMock('SeanStewart\PlanConfig\PlanConfig', ['getPlan'], [$this->config, $this->auth]);

        $planConfig->expects($this->exactly(2))
                   ->method('getPlan')
                   ->with('plan')
                   ->willReturnOnConsecutiveCalls(['foo' => ['bar' => 'value']], ['foo' => 'bar']);

        $this->assertEquals('value', $planConfig->getPlanKey('foo.bar', 'plan'));
        $this->assertFalse($planConfig->getPlanKey('foo.bar', 'plan'));
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


}