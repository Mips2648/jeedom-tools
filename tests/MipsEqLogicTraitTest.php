<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/MipsEqLogicTrait.php';

class MipsEqLogicTraitTest extends TestCase {

    // tests on GetCommandsFileContent
    public function testGetCommandsFileContent() {
        $trait = new class {
            use MipsEqLogicTrait {
                getCommandsFileContent as public; // make the method public
            }
        };

        // $trait = $this->getMockForTrait(MipsEqLogicTrait::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Fichier de configuration non trouvé:tests/unit/commands.json");
        $trait::getCommandsFileContent('tests/unit/commands.json');
    }

    // tests on getRequiredPackageDetail positive cases
    public function testgetRequiredPackageDetail_approx() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon ~=1.0.0', $details);
        $this->assertTrue($result);
        $this->assertEquals('jeedomdaemon', $details[1]);
        $this->assertEquals('~=', $details[2]);
        $this->assertEquals('1.0.0', $details[3]);
    }

    public function testgetRequiredPackageDetail_greaterthan() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon>= 1.0.0', $details);
        $this->assertTrue($result);
        $this->assertEquals('jeedomdaemon', $details[1]);
        $this->assertEquals('>=', $details[2]);
        $this->assertEquals('1.0.0', $details[3]);
    }

    public function testgetRequiredPackageDetail_extra() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('pymodbus[serial] == 3.7.3', $details);
        $this->assertTrue($result);
        $this->assertEquals('pymodbus[serial]', $details[1]);
        $this->assertEquals('==', $details[2]);
        $this->assertEquals('3.7.3', $details[3]);
    }

    public function testgetRequiredPackageDetail_equal() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon==1.0.0', $details);
        $this->assertTrue($result);
        $this->assertEquals('jeedomdaemon', $details[1]);
        $this->assertEquals('==', $details[2]);
        $this->assertEquals('1.0.0', $details[3]);
    }

    // tests on getRequiredPackageDetail negative cases
    public function testgetRequiredPackageDetail_lowerthan() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon<=1.0.0', $details);
        $this->assertFalse($result); //because the syntax is not supported
    }

    public function testgetRequiredPackageDetail_wrongSyntax() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon=1.0.0', $details);
        $this->assertFalse($result);
    }

    public function testgetRequiredPackageDetail_invalidPackage() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon', $details);
        $this->assertFalse($result);
    }

    // tests on getInstalledPackageDetail
    public function testgetInstalledPackageDetail_present() {
        $trait = new class {
            use MipsEqLogicTrait {
                getInstalledPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getInstalledPackageDetail('jeedomdaemon', 'jeedomdaemon==1.1.0||anotherpackage==1.1.1', $details);
        $this->assertTrue($result);
        $this->assertEquals('jeedomdaemon==1.1.0', $details[0]);
        $this->assertEquals('1.1.0', $details[1]);
    }

    // tests on getInstalledPackageDetail
    public function testgetInstalledPackageDetail_present_mix_case() {
        $trait = new class {
            use MipsEqLogicTrait {
                getInstalledPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getInstalledPackageDetail('unidecode', 'Unidecode==1.1.0||anotherpackage==1.1.1', $details);
        $this->assertTrue($result);
        $this->assertEquals('Unidecode==1.1.0', $details[0]);
        $this->assertEquals('1.1.0', $details[1]);
    }

    public function testgetInstalledPackageDetail_absent() {
        $trait = new class {
            use MipsEqLogicTrait {
                getInstalledPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getInstalledPackageDetail('jeedomdaemon', 'anotherpackage==1.1.1', $details);
        $this->assertFalse($result);
    }
}
