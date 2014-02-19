<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp
 */
namespace MentorApp;

class SkillArraySerializer implements SerializerInterface
{
    /**
     * Method to convert an instance of Skill into an array with the values
     *
     * @param \MentorApp\Skill $skill
     * @return array
     */
    public function toArray($skill)
    {
        $response = [];
        if (!$skill instanceof \MentorApp\Skill) {
            return $response;
        }
        $response['id'] = htmlspecialchars($skill->id);
        $response['name'] = htmlspecialchars($skill->name);
        $response['added'] = htmlspecialchars($skill->added->format('Y-m-d H:i:s'));
        return $response;
    }

    public function fromArray(Array $skillArray)
    {
        $skill = new Skill();
        $skill->id = (isset($skillArray['id'])) ? $skillArray['id'] : null;
        $skill->name = (isset($skillArray['name'])) ? $skillArray['name'] : null;
        $skill->added = (isset($skillArray['added'])) ? $skillArray['added'] : null;
        return $skill;
    }
}
