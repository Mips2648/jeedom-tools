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
        $this->assertCount(7, $details);
        $this->assertEquals('jeedomdaemon', $details[1]);
        $this->assertEquals('jeedomdaemon', $details['name']);
        $this->assertEquals('~=', $details[2]);
        $this->assertEquals('~=', $details['operator']);
        $this->assertEquals('1.0.0', $details[3]);
        $this->assertEquals('1.0.0', $details['version']);
    }

    public function testgetRequiredPackageDetail_greaterthan() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon>= 1.0.0', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('jeedomdaemon', $details[1]);
        $this->assertEquals('jeedomdaemon', $details['name']);
        $this->assertEquals('>=', $details[2]);
        $this->assertEquals('>=', $details['operator']);
        $this->assertEquals('1.0.0', $details[3]);
        $this->assertEquals('1.0.0', $details['version']);
    }

    public function testgetRequiredPackageDetail_greater_or_equal() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('requests>=2.31', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('requests', $details[1]);
        $this->assertEquals('requests', $details['name']);
        $this->assertEquals('>=', $details[2]);
        $this->assertEquals('>=', $details['operator']);
        $this->assertEquals('2.31', $details[3]);
        $this->assertEquals('2.31', $details['version']);
    }

    public function testgetRequiredPackageDetail_extra() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('pymodbus[serial] == 3.7.3', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('pymodbus', $details[1]);
        $this->assertEquals('pymodbus', $details['name']);
        $this->assertEquals('==', $details[2]);
        $this->assertEquals('==', $details['operator']);
        $this->assertEquals('3.7.3', $details[3]);
        $this->assertEquals('3.7.3', $details['version']);
    }

    public function testgetRequiredPackageDetail_extra_space() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('pymodbus [serial] == 3.7.3', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('pymodbus', $details[1]);
        $this->assertEquals('pymodbus', $details['name']);
        $this->assertEquals('==', $details[2]);
        $this->assertEquals('==', $details['operator']);
        $this->assertEquals('3.7.3', $details[3]);
        $this->assertEquals('3.7.3', $details['version']);
    }

    public function testgetRequiredPackageDetail_extra_set() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('SomeProject[foo, bar] >= 2.0', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('SomeProject', $details[1]);
        $this->assertEquals('SomeProject', $details['name']);
        $this->assertEquals('>=', $details[2]);
        $this->assertEquals('>=', $details['operator']);
        $this->assertEquals('2.0', $details[3]);
        $this->assertEquals('2.0', $details['version']);
    }

    public function testgetRequiredPackageDetail_environment_markers() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('requests [security] >= 2.8.1, == 2.8.* ; python_version < "2.7"', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('requests', $details[1]);
        $this->assertEquals('requests', $details['name']);
        $this->assertEquals('>=', $details[2]);
        $this->assertEquals('>=', $details['operator']);
        $this->assertEquals('2.8.1', $details[3]);
        $this->assertEquals('2.8.1', $details['version']);
    }

    public function testgetRequiredPackageDetail_equal() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon==1.0.0', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('jeedomdaemon', $details[1]);
        $this->assertEquals('jeedomdaemon', $details['name']);
        $this->assertEquals('==', $details[2]);
        $this->assertEquals('==', $details['operator']);
        $this->assertEquals('1.0.0', $details[3]);
        $this->assertEquals('1.0.0', $details['version']);
    }

    public function testgetRequiredPackageDetail_package_with_hyphen() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('python-slugify~=8.0.4', $details);
        $this->assertTrue($result);
        $this->assertCount(7, $details);
        $this->assertEquals('python-slugify', $details[1]);
        $this->assertEquals('python-slugify', $details['name']);
        $this->assertEquals('~=', $details[2]);
        $this->assertEquals('~=', $details['operator']);
        $this->assertEquals('8.0.4', $details[3]);
        $this->assertEquals('8.0.4', $details['version']);
    }

    public function testgetRequiredPackageDetail_only_name() {
        $trait = new class {
            use MipsEqLogicTrait {
                getRequiredPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getRequiredPackageDetail('jeedomdaemon', $details);
        $this->assertTrue($result);
        $this->assertCount(3, $details);
        $this->assertEquals('jeedomdaemon', $details[1]);
        $this->assertEquals('jeedomdaemon', $details['name']);
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

        $result = $trait->getRequiredPackageDetail('_someproject', $details);
        $this->assertFalse($result);
    }

    public function testgetInstalledPackageDetail_present() {
        $trait = new class {
            use MipsEqLogicTrait {
                getInstalledPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getInstalledPackageDetail('jeedomdaemon', ['jeedomdaemon==1.1.0', 'anotherpackage==1.1.1'], $details);
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

        $result = $trait->getInstalledPackageDetail('unidecode', ['Unidecode==1.1.0', 'anotherpackage==1.1.1'], $details);
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

        $result = $trait->getInstalledPackageDetail('jeedomdaemon', ['anotherpackage==1.1.1'], $details);
        $this->assertFalse($result);
    }

    public function testgetInstalledPackageDetail_absent_hyphen() {
        $trait = new class {
            use MipsEqLogicTrait {
                getInstalledPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getInstalledPackageDetail('Unidecode', ['requests-toolbelt==1.0.0', 'text-unidecode==1.3'], $details);
        $this->assertFalse($result);
    }

    public function testgetInstalledPackageDetail_absent_hyphen2() {
        $trait = new class {
            use MipsEqLogicTrait {
                getInstalledPackageDetail as public; // make the method public
            }
        };

        $result = $trait->getInstalledPackageDetail('requests', ['requests-toolbelt==1.0.0', 'text-unidecode==1.3'], $details);
        $this->assertFalse($result);
    }
}
