<?php
namespace MentorApp;

class PartnershipTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Method to set up the dependencies for use in the test
     */
    public function setUp()
    {
        $this->db = $this->getMock('\PDOTestHelper', ['prepare']);
        $this->statement = $this->getMock('\PDOStatement', ['execute', 'rowCount', 'fetch']);
        $this->mockData = array();
        $this->mockData['id'] = 'abefa32120';
        $this->mockData['id_apprentice'] = 'abcdef1234';
        $this->mockData['id_mentor'] = '11223344ba';
    }

    /**
     * Method to tear down the dependencies at the end of each test
     */
    public function tearDown()
    {
        unset($this->db);
        unset($this->statement);
    }

    /**
     * Test to create a partnership between 2 users
     */
    public function testCreatePartnership()
    {
        $mentor = new User();
        $mentor->id = '1aef234567';
        $apprentice = new User();
        $apprentice->id = '12aef34568';
        $query = "INSERT INTO partnership (id, id_mentor, id_apprentice) VALUES (:id, :mentor, :apprentice)";
        $query .= " ON DUPLICATE KEY UPDATE id_mentor = :mentor";

        // stubs for the Hash functionality
        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with('SELECT id FROM `partnership` WHERE id = :id')
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(1))
            ->method('rowCount')
            ->will($this->returnValue(0));

        // stubs for the save functionality
           $this->db->expects($this->at(1))
            ->method('prepare')
            ->with($query)
            ->will($this->returnValue($this->statement));
         $this->statement->expects($this->at(2))
            ->method('execute')
            ->with($this->arrayHasKey('id'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(3))
            ->method('rowCount')
            ->will($this->returnValue(1));
        
        $partnershipManager = new PartnershipManager($this->db);
        $result = $partnershipManager->create($mentor, $apprentice);
        $this->assertTrue($result);
    }

    /**
     * Test to ensure false is return when there are 0 affected rows
     */
    public function testCreatePartnershipInsertFails()
    {
        $mentor = new User();
        $mentor->id = '1234567';
        $apprentice = new User();
        $apprentice->id = '1234568';
        $query = "INSERT INTO partnership (id, id_mentor, id_apprentice) VALUES (:id, :mentor, :apprentice)";
        $query .= " ON DUPLICATE KEY UPDATE id_mentor = :mentor";
        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with('SELECT id FROM `partnership` WHERE id = :id')
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(0))
            ->method('rowCount')
            ->will($this->returnValue(0));
        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with($query)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(2))
            ->method('execute')
            ->with($this->arrayHasKey('id'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(1))
            ->method('rowCount')
            ->will($this->returnValue(0));
        $partnershipManager = new PartnershipManager($this->db);
        $result = $partnershipManager->create($mentor, $apprentice);
        $this->assertFalse($result);
    }

    /**
     * Test to ensure that when an exception is thrown it is caught and
     * returns false
     * @expectedException \PDOException
     */
    public function testExceptionReturnsFalse()
    {
        $mentor = new User();
        $mentor->id = '1234567890';
        $apprentice = new User();
        $apprentice->id = '12344';
        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with('SELECT id FROM `partnership` WHERE id = :id')
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('execute')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));
        $this->db->expects($this->at(1))
            ->method('prepare')
            ->will($this->throwException(new \PDOException));
        $partnershipManager = new PartnershipManager($this->db);
        $result = $partnershipManager->create($mentor, $apprentice);
    }

    /**
     * Test to ensure that a partnership can be deleted
     */
    public function testPartnershipDeleted()
    {
        $id = '12345678';
        $query = "DELETE FROM partnership WHERE id = :id";
        $this->db->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('execute')
            ->with(['id' => $id])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));
        $partnershipManager = new PartnershipManager($this->db);
        $result = $partnershipManager->delete($id);
        $this->assertTrue($result);
    }

    /**
     * Test to ensure that a partnership returns false if there are zero
     * affected rows
     */
    public function testPartnershipDeletedNoAffectedRows()
    {
        $id = '12345678';
        $query = "DELETE FROM partnership WHERE id = :id";
        $this->db->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('execute')
            ->with(['id' => $id])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));
        $partnershipManager = new PartnershipManager($this->db);
        $result = $partnershipManager->delete($id);
        $this->assertFalse($result);
    }

    /**
     * Test to ensure false is returned when PDO throws an exception
     * @expectedException \PDOException
     */
    public function testDeleteReturnsFalseOnException()
    {
        $id = 12345;
        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException));
        $partnershipManager = new PartnershipManager($this->db);
        $result = $partnershipManager->delete($id);
    }

    /**
     * Test to ensure that a partnership can be retrieved by id
     */
    public function testCanRetrievePartnershipById()
    {
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM partnership WHERE id = :id')
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('execute')
            ->with(['id' => $this->mockData['id']])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($this->mockData));
        $manager = new PartnershipManager($this->db);
        $partnership = $manager->retrieveById($this->mockData['id']);

        $this->assertEquals($partnership->id, $this->mockData['id']);
        $this->assertEquals($partnership->mentor, $this->mockData['id_mentor']);
        $this->assertEquals($partnership->apprentice, $this->mockData['id_apprentice']);
    }

    /**
     * Test to ensure an invalid hash returns null
     */
    public function testInvalidHashReturnsNull()
    {
        $id = '12345679';
        $manager = new PartnershipManager($this->db);
        $partnership = $manager->retrieveById($id);
        $this->assertNull($partnership);
    }

    /**
     * Test to ensure that a PDO Exception returns null
     * @expectedException \PDOException
     */
    public function testPDOExceptionReturnsNull()
    {
        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException("Random PDO Exception")));
        $id = 'ab3212aebc';
        $manager = new PartnershipManager($this->db);
        $partnership = $manager->retrieveById($id);
    }

    /**
     * Test to ensure partnerships can be retrieved by mentor
     */
    public function testPartnershipsCanBeRetrievedByMentor()
    {
         $mentor_id = 'ab32aced89';
         $apprentices = array();
         $apprentices[] = '12904adefc';
         $apprentices[] = '6782abc56f';
         $ids = array();
         $ids[] = 'bc345aed98';
         $ids[] = 'ffa3290cde';
         $this->db->expects($this->once())
             ->method('prepare')
             ->with('SELECT * FROM `partnership` WHERE id_mentor = :mentor_id')
             ->will($this->returnValue($this->statement));
         $this->statement->expects($this->once())
             ->method('execute')
             ->with(['mentor_id' => $mentor_id])
             ->will($this->returnValue($this->statement));
         $this->statement->expects($this->at(1))
             ->method('fetch')
			 ->will($this->returnValue(['id' => $ids[0], 'id_mentor' => $mentor_id, 'id_apprentice' => $apprentices[0]]));
         $this->statement->expects($this->at(2))
             ->method('fetch')
			 ->will($this->returnValue(['id' => $ids[1], 'id_mentor' => $mentor_id, 'id_apprentice' => $apprentices[1]]));
         $this->statement->expects($this->at(3))
             ->method('fetch')
			 ->will($this->returnValue(false));
         $manager = new PartnershipManager($this->db);
         $partnerships = $manager->retrieveByMentor($mentor_id);

         $this->assertEquals($partnerships[0]->id, $ids[0]);
         $this->assertEquals($partnerships[1]->id, $ids[1]);
         $this->assertEquals($partnerships[0]->apprentice, $apprentices[0]);
         $this->assertEquals($partnerships[1]->apprentice, $apprentices[1]);
    }

    /**
     * Test to ensure an invalid hash returns an empty array
     */
    public function testInvalidMentorHashReturnsEmptyArray()
    {
        $mentor_id = 'acef1234';
        $manager = new PartnershipManager($this->db);
        $partnerships = $manager->retrieveByApprentice($mentor_id);
        $this->assertEmpty($partnerships);
    }

    /**
     * Test to ensure PDO Exception is thrown up to the top
     * @expectedException \PDOException
     */
    public function testPDOExceptionMentorIsThrown()
    {
        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException('Random Exception')));
        $mentor_id = 'abe123ce98';
        $manager = new PartnershipManager($this->db);
        $partnerships = $manager->retrieveByMentor($mentor_id);
        $this->assertEmpty($partnerships);
    }

    /**
     * Test to ensure partnerships can be retrieved by apprentice
     */
    public function testPartnershipsCanBeRetrievedByApprentice()
    {
        $apprentice = 'bbccddee65';
        $mentors = array();
        $mentors[] = '65190bcea9';
        $mentors[] = 'bb831aef33';
        $ids = array();
        $ids[] = 'fe7845badf';
        $ids[] = 'ce614ffe98';
        $this->db->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `partnership` WHERE id_apprentice = :apprentice_id')
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->once())
            ->method('execute')
            ->with(['apprentice_id' => $apprentice])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue(['id' => $ids[0], 'id_mentor' => $mentors[0], 'id_apprentice' => $apprentice]));
        $this->statement->expects($this->at(2))
            ->method('fetch')
            ->will($this->returnValue(['id' => $ids[1], 'id_mentor' => $mentors[1], 'id_apprentice' => $apprentice]));
        $this->statement->expects($this->at(3))
            ->method('fetch')
            ->will($this->returnValue(false));
        $manager = new PartnershipManager($this->db);
        $partnerships = $manager->retrieveByApprentice($apprentice);

        $this->assertEquals($partnerships[0]->id, $ids[0]);
        $this->assertEquals($partnerships[0]->mentor, $mentors[0]);
        $this->assertEquals($partnerships[1]->id, $ids[1]);
        $this->assertEquals($partnerships[1]->mentor, $mentors[1]);
    }

    /**
     * Test to ensure an invalid hash returns empty array
     */
    public function testInvalidApprenticeHashReturnsEmptyArray()
    {
        $apprentice = 'abcdef123';
        $manager = new PartnershipManager($this->db);
        $partnerships = $manager->retrieveByApprentice($apprentice);
        $this->assertEmpty($partnerships);
    }

    /**
     * Test to ensure a PDO Exception is thrown to the top
     * @expectedException \PDOException
     */
    public function testPDOExceptionApprenticeIsThrown()
    {
        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException('Random PDO Exception')));
        $apprentice = 'bb432afe44';
        $manager = new PartnershipManager($this->db);
        $partnerships = $manager->retrieveByApprentice($apprentice);
        $this->assertEmpty($partnerships);
    }
}

