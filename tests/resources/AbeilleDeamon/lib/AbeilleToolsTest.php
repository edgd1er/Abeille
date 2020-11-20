<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AbeilleToolsTest extends TestCase
{


    public function testGetNumberFromLevel()
    {

        $this->assertEquals(0, AbeilleTools::getNumberFromLevel('NONE'));
        $this->assertEquals(1, AbeilleTools::getNumberFromLevel('ERROR'));
        $this->assertEquals(2, AbeilleTools::getNumberFromLevel('WARNING'));
        $this->assertEquals(3, AbeilleTools::getNumberFromLevel('INFO'));
        $this->assertEquals(4, AbeilleTools::getNumberFromLevel('DEBUG'));

    }


    final function getPluginLogLevelTest() {

        // var_dump( config::getLogLevelPlugin()["log::level::Abeille"] );
        // si debug:  {"100":"1","200":"0","300":"0","400":"0","1000":"0","default":"0"}
        // si info:   {"100":"0","200":"1","300":"0","400":"0","1000":"0","default":"0"}
        // si warning:{"100":"0","200":"0","300":"1","400":"0","1000":"0","default":"0"}
        // si error:  {"100":"0","200":"0","300":"0","400":"1","1000":"0","default":"0"}
        // si aucun:  {"100":"0","200":"0","300":"0","400":"0","1000":"1","default":"0"}
        // si defaut: {"100":"0","200":"0","300":"0","400":"0","1000":"0","default":"1"}
        //$logLevelPluginJson = config::getLogLevelPlugin()["log::level::Abeille"];
        if ($logLevelPluginJson['100']) return 4;
        if ($logLevelPluginJson['200']) return 3;
        if ($logLevelPluginJson['300']) return 2;
        if ($logLevelPluginJson['400']) return 1;
        if ($logLevelPluginJson['1000']) return 0;
        if ($logLevelPluginJson['default']) return 1; // This one is set to 1 but should be found from conf
    }
}
