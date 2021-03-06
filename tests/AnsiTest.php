<?php

require_once 'TestBase.php';

use League\CLImate\TerminalObject\Router\BasicRouter;
use League\CLImate\Decorator\Style;
use League\CLImate\Decorator\Tags;
use League\CLImate\Decorator\Parser\NonAnsi;
use League\CLImate\Decorator\Parser\Ansi;
use League\CLImate\Decorator\Parser\ParserFactory;

class AnsiTest extends TestBase
{

    /** @test */

    public function it_can_output_with_ansi()
    {
        $router = new BasicRouter();
        $router->output($this->output);

        $style  = new Style();
        $parser = new Ansi($style->current(), new Tags($style->all()));

        $obj = Mockery::mock('League\CLImate\TerminalObject');
        $obj->shouldReceive('result')->once()->andReturn("<green>I am green</green>");
        $obj->shouldReceive('sameLine')->once()->andReturn(false);
        $obj->shouldReceive('getParser')->once()->andReturn($parser);

        $this->shouldWrite("\e[m\e[32mI am green\e[0m\e[0m");

        $router->execute($obj);
    }

    /** @test */

    public function it_can_output_without_ansi()
    {
        $router = new BasicRouter();
        $router->output($this->output);

        $style  = new Style();
        $parser = new NonAnsi($style->current(), new Tags($style->all()));

        $obj = Mockery::mock('League\CLImate\TerminalObject');
        $obj->shouldReceive('result')->once()->andReturn("<green>I am not green</green>");
        $obj->shouldReceive('sameLine')->once()->andReturn(false);
        $obj->shouldReceive('getParser')->once()->andReturn($parser);

        $this->shouldWrite("I am not green");

        $router->execute($obj);
    }

    /** @test */

    public function it_will_recognize_non_ansi_systems()
    {
        $system = Mockery::mock('League\CLImate\Util\System\Windows');
        $system->shouldReceive('hasAnsiSupport')->andReturn(false);

        $parser = ParserFactory::getInstance($system, [], new Tags([]));

        $this->assertInstanceOf('League\CLImate\Decorator\Parser\NonAnsi', $parser);
    }

    /** @test */

    public function it_will_force_ansi_on_a_non_ansi_system()
    {
        $system = new League\CLImate\Util\System\Windows();
        $util   = new \League\CLImate\Util\UtilFactory($system);
        $this->cli->setUtil($util);
        $this->cli->forceAnsiOn();

        $this->shouldWrite("\e[m\e[32mI am green\e[0m\e[0m");

        $this->cli->out("<green>I am green</green>");
    }

    /** @test */

    public function it_will_force_ansi_off_on_an_ansi_system()
    {
        $system = new League\CLImate\Util\System\Linux();
        $util   = new \League\CLImate\Util\UtilFactory($system);
        $this->cli->setUtil($util);
        $this->cli->forceAnsiOff();

        $this->shouldWrite("I am green");

        $this->cli->out("<green>I am green</green>");
    }

}
