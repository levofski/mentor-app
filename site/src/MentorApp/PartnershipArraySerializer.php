<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp
 */
namespace MentorApp;

class PartnershipArraySerializer implements SerializerInterface
{
    /**
     * Converts a Partnership instance into an array
     *
     * @param \MentorApp\Partnership $partnership
     * @return array
     */
    public function toArray($partnership)
    {
        $response = [];
        if (!$partnership instanceof \MentorApp\Partnership) {
            return $response;
        }
        $response['id'] = $partnership->id;
        $response['mentor'] = $partnership->mentor;
        $response['apprentice'] = $partnership->apprentice;
        return $response;
    }

    /**
     * Converts an array of partnership information into a Partnership instance
     *
     * @param array $partnershipValues
     * @return \MentorApp\Partnership
     */
    public function fromArray(array $partnershipValues)
    {
        $partnership = new Partnership();
        $partnership->id = (isset($partnershipValues['id'])) ? $partnershipValues['id'] : null;
        $partnership->mentor = (isset($partnershipValues['mentor'])) ? $partnershipValues['mentor'] : null;
        $partnership->apprentice = (isset($partnershipValues['apprentice'])) ? $partnershipValues['apprentice'] : null;
        return $partnership;
    }
}
