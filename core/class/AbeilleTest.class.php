<?php

// Depuis le shell
// phpunit delestageTest.class.php

use PHPUnit\Framework\TestCase;

require_once './Abeille.class.php';

class AbeilleTest extends TestCase {

    function test_phpSyntax() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );
        exec('php Abeille.class.php', $output, $retval);
        $this->assertSame( 0, count($output) );
        $this->assertSame( 0, $retval );
    }

    function test_volt2pourcent() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );
        $this->assertSame(    0, Abeille::volt2pourcent(2700) );
        $this->assertSame( 60.0, Abeille::volt2pourcent(3000) );
        $this->assertSame(  100, Abeille::volt2pourcent(4000) );
    }

    function test_health() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );
        $result = '[{"test":"Ports: ","result":"nonenone\/dev\/ttyUSB0nonenonenonenonenonenonenone","advice":"Ports utilis\u00e9s","state":true}]';
        $this->assertSame( $result, json_encode(Abeille::health()) );
    }

    function test_deamon_info() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );
        $result = '{"state":"ok","launchable":"ok","launchable_message":""}';
        $this->assertSame( $result, json_encode(Abeille::deamon_info()) );
    }

    function test_getIEEE() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );

        $address = "Abeille3/3B02";
        $IEEE_Result  = "14B457FFFE79EBA9";

        $abeille = new Abeille;

        $this->assertSame( $IEEE_Result, $abeille->getIEEE($address) );
        

    }

    function test_getEqFromIEEE() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );

        $IEEE_Ok  = "14B457FFFE79EBA9";
        $IEEE_NOk = "0000000000000000";

        $abeille = new Abeille;

        $this->assertSame( "Abeille3-1255", $abeille->getEqFromIEEE($IEEE_Ok)->getName() );
        $this->assertSame( null, $abeille->getEqFromIEEE($IEEE_NOk) );

    }

    function test_checkInclusionStatus() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );
        $destOk = "Abeille3";
        $detNok = "AbeilleY";

        $abeille = new Abeille;

        $this->assertSame( 0, $abeille->checkInclusionStatus($destOk) );
        $this->assertSame(-1, $abeille->checkInclusionStatus($detNok) );
    }

    function test_createRuche() {
        fwrite(STDOUT, "\n\n" . __METHOD__ );
        $dest = "AbeilleX";

        $abeille = new Abeille;

        $this->assertSame( "1254", $abeille->byLogicalId("Abeille3/0000","Abeille")->getId() );
        $this->assertSame( false, $abeille->byLogicalId($dest."/0000","Abeille") );
        $abeille->createRuche($dest);
        $this->assertSame( "Ruche-".$dest, $abeille->byLogicalId($dest."/0000","Abeille")->getName() );
        
    }
}


?>
