<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp;
 */
namespace MentorApp;

class SkillSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup method - called before each test
     */
    public function setUp()
    {
        $this->serializer = new SkillArraySerializer();
    }

    /**
     * Teardown method - called after each test
     */
    public function tearDown()
    {
        unset($this->serializer);
    }

    /**
     * Test for toArray ensures when a skill is passed in, that an array is returned
     */ 
    public function testSkillIsReturnedAsArray()
    {
        $skill = new Skill();
        $skill->id = '1bced32ead';
        $skill->name = 'Nunchuck';
        $skill->added = new \DateTime('2013-11-26 21:08:15');
        $skill->authorized = true;

        $skillArray = $this->serializer->toArray($skill);
        $this->assertEquals($skill->id, $skillArray['id']);
        $this->assertEquals($skill->name, $skillArray['name']);
        $this->assertEquals($skill->added->format('Y-m-d H:i:s'), $skillArray['added']);
    }

    /**
     * Test to ensure if a skill isn't passed in that an empty array is returned
     */
    public function testInputDifferentTypeReturnsEmptyArray()
    {
        $skill = new \stdClass();
        $array = $this->serializer->toArray($skill);
        $this->assertEmpty($array);
    }

    /**
     * Test to ensure that when an array of skill data is passed in a skill
     * instance is returned
     */
    public function testEnsureSkillArrayReturnsSkillInstance()
    {
        $skillArray = array(
            'id' => 'bade1123de',
            'name' => 'Bow Staff',
            'added' => '2013-11-26 21:23:50',
            'authorized' => true
        );

        $skill = $this->serializer->fromArray($skillArray);
        $this->assertEquals($skillArray['id'], $skill->id);
        $this->assertEquals($skillArray['name'], $skill->name);
        $this->assertEquals($skillArray['added'], $skill->added);
        $this->assertNull($skill->authorized);
    }
}
