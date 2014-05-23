<?php
/**
 * @author PHPMentoring
 * @licence http://opensource.org/licences/MIT MIT
 * @package MentorApp
 */

namespace MentorApp;

class SkillServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup mock dependencies for the test
     */
    protected function setUp()
    {
        $this->db = $this->getMock('\PDOTestHelper', array('prepare'));
        $this->statement = $this->getMock('\PDOStatement', array('execute', 'fetch', 'fetchAll', 'rowCount'));
    }

    /**
     * Tear Down the mock dependencies for the class
     */
    protected function tearDown()
    {
       unset($this->db);
       unset($this->statement);
    } 

    /**
     * Test to ensure that the retrieve method returns a populate
     * skill object when a skill matching the search term exists
     */
    public function testRetrieveReturnsPopulatedSkillEntity()
    {
        $skillData = array(
            'id' => null,
            'name' => 'Testing',
            'added' => '2013-10-01 09:34:03',
            'authorized' => true,
        );
        $this->statement->expects($this->once())
            ->method('execute');

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($skillData));

        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($this->statement));

        $service = new SkillService($this->db);
        $skill = $service->retrieve($skillData['name']);

        $this->assertEquals($skillData['name'], $skill->name);
        $this->assertTrue($skill->authorized);
    }

    /**
     * Test to ensure that null is return if no skill can be found by with
     * the search term
     */
    public function testRetrieveReturnsNullIfSkillNotFound()
    {
        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($this->statement));

        $service = new SkillService($this->db);

        $this->assertNull($service->retrieve('Skill Does Not Exist'));
    }

    /**
     * Test to ensure that a search with the correct term will return
     * the approriate Skill objects
     */
    public function testSearchByTermReturnsArrayOfSkillMatchingTerm()
    {
        $skillData = array(
            array('id' => 'abc123def4', 'name' => 'Test Skill 1', 'added' => '2013-10-01', 'authorized' => 1),
            array('id' => 'abad1f567e', 'name' => 'Test Skill 2', 'added' => '2013-10-02', 'authorized' => 1),
            array('id' => 'fe12cdab7e', 'name' => 'Test Skill 3', 'added' => '2013-10-03', 'authorized' => 0),
        );

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(count($skillData)));

        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(2))
            ->method('fetch')
            ->will($this->returnValue($skillData[0]));

        $this->statement->expects($this->at(3))
            ->method('fetch')
            ->will($this->returnValue($skillData[1]));

        $this->statement->expects($this->at(4))
            ->method('fetch')
            ->will($this->returnValue($skillData[2]));

        $service = new SkillService($this->db);
        $skills = $service->searchByTerm('Test');

        $this->assertCount(count($skillData), $skills);
        $this->assertEquals($skillData[0]['name'], $skills[0]->name);
        $this->assertEquals($skillData[1]['id'], $skills[1]->id);
        $this->assertEquals($skillData[2]['id'], $skills[2]->id);
    }

    /**
     * Test to ensure that an empty array is return when no matching
     * skills are found
     */
    public function testSearchByTermReturnsEmptyArrayWhenNoMatchingSkillsFound()
    {
        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($this->statement));

        $service = new SkillService($this->db);
        $skills = $service->searchByTerm('Unused Term');
        $this->assertEmpty($skills);
    }

    /**
     * Test to ensure the save method functions properly when the Skill has an ID set
     */
    public function testEnsureSaveMethodUpdatesTable()
    {
        $skill = new Skill();
        $skill->id = 'ab12e4bb12';
        $skill->name = 'PHP';
        $skill->added = '2013-11-18 20:58:12';
        $skill->authorized = true;

        $query = 'INSERT INTO `skill` (
            `id`,
            `name`,
            `authorized`,
            `added`
        ) VALUES (
            :id,
            :name,
            :authorized,
            :added
        ) ON DUPLICATE KEY UPDATE
            `authorized` = :authorized
        ';
        $this->db->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->once())
            ->method('execute')
            ->with(['id' => $skill->id, 'name' => $skill->name, 'authorized' => $skill->authorized, 'added' => $skill->added])
            ->will($this->returnValue($this->statement));
        $skillService = new SkillService($this->db);
        $savedSkill = $skillService->save($skill);
        $this->assertTrue($savedSkill, 'Save method returned false');
    }

    /**
     * Test to ensure that a skill with no id has an ID created and the save
     * completes correctly
     */
    public function testEnsureThatNewObjectIsCreatedWithNoIdValue()
    {
        $skill = new Skill();
        $skill->name = 'OOP';
        $skill->authorized = true;
        $skill->added = '2013-11-18 21:18:30';

        $query = 'INSERT INTO `skill` (
            `id`,
            `name`,
            `authorized`,
            `added`
        ) VALUES (
            :id,
            :name,
            :authorized,
            :added
        ) ON DUPLICATE KEY UPDATE
            `authorized` = :authorized
        ';
        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with('SELECT id FROM `skill` WHERE id = :id')
            ->will($this->returnValue($this->statement)); 
        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with($query)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(2))
            ->method('execute')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $skillService = new SkillService($this->db);
        $skillReturn = $skillService->save($skill);
        $this->assertTrue($skillReturn);
    }

    /**
     * Test to ensure that when a \PDO Exception is thrown in save when
     * updating a skill entry returns false
     * @expectedException \PDOException
     */
    public function testSaveReturnsFalseOnPDOException()
    {
        $skill = new Skill();
        $skill->id = '123edf23ea';
        $skill->name = 'TDD';
        $skill->authorized = false;
        $skill->added = '2013-11-19 13:56:12';

        $query = 'INSERT INTO `skill` (
            `id`,
            `name`,
            `authorized`,
            `added`
        ) VALUES (
            :id,
            :name,
            :authorized,
            :added
        ) ON DUPLICATE KEY UPDATE
            `authorized` = :authorized
        ';
        $this->db->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->will($this->returnValue($this->statement));
 
        $this->statement->expects($this->once())
            ->method('execute')
            ->will($this->throwException(new \PDOException));
            
        $skillService = new SkillService($this->db);
        $skillReturn = $skillService->save($skill);
        $this->assertFalse($skillReturn);
    }

    /**
     * Test to ensure that when a PDO exeception is encountered in exists and
     * throws a \RuntimeException
     *
     * @expectedException \RuntimeException
     */
    public function testEnsureRuntimeExceptionIsThrownOnPDOExceptionInExists()
    {
        $skill = new Skill();
        $skill->name = 'TDD';
        $skill->authorized = true;
        $skill->added = '2013-11-18 21:25:30';

        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException));

        $skillService = new SkillService($this->db);
        $skillReturn = $skillService->save($skill);
    }
}
