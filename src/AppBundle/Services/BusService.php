<?php

namespace AppBundle\Services;

use AppBundle\Entity\BusEntry;
use AppBundle\Entity\BusLine;
use AppBundle\Entity\BusStop;
use Doctrine\ORM\EntityManager;
use Goutte\Client;

class BusService
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * update entries in database
     */
    public function updateEntries()
    {
        $client = new Client();
        //downloading main page for bus stops
        $crawler = $client->request("GET", "http://rozklady.mpk.krakow.pl/aktualne/przystan.htm");

        //we store entites in here
        $entries = array("lines"=>array(), "busStops" => array());

        //deleting old data from database
        $this->em->getConnection()->beginTransaction();
        $this->em->createQuery("DELETE FROM AppBundle:BusEntry")->execute();
        $this->em->createQuery("DELETE FROM AppBundle:BusStop")->execute();
        $this->em->createQuery("DELETE FROM AppBundle:BusLine")->execute();
        $this->em->getConnection()->prepare("ALTER TABLE bus_entry AUTO_INCREMENT = 1")->execute();
        $this->em->getConnection()->prepare("ALTER TABLE bus_stop AUTO_INCREMENT = 1")->execute();
        $this->em->getConnection()->prepare("ALTER TABLE bus_line AUTO_INCREMENT = 1")->execute();
        $count = 0;
        //iterating bus stops
        $crawler->filter("table a")->each(function ($node) use(&$count, &$entries, $client) {
            if ($node->text()) {
                if ($count >= 20) {
                    return;
                }
//                echo $count . "\n";
                $count++;
                $busStopName = $node->text();
                $crawler = $client->click($node->link());
                $lines = array();
                $crawler->filter("a")->each(function ($node) use(&$lines, $client) {
                    $uri = $node->link()->getUri();
                    $line = $node->text();
                    if ("Inne przystanki" == $line) {
                        return;
                    }
                    $uriParts = explode("/", $uri);
                    $uriParts[count($uriParts) - 1] = str_replace("r","t", $uriParts[count($uriParts) - 1]);
                    $crawler = $client->request("GET", implode("/", $uriParts));
                    preg_match_all("/fonthour\">([0-9]+).*\n.*fontmin\">([^<]+)/", $crawler->html(), $matches);
                    $hours = array('normal' => array(), 'saturday' => array(), 'sunday' => array(),);
                    foreach ($matches[1] as $key => $match) {
                        if (trim($matches[2][$key]) == "-") {
                            $minutes = array();
                        } else {
                            $minutes = explode(" ", $matches[2][$key]);
                            $minutes = array_filter($minutes);
                        }
                        $day = BusEntry::TYPE_NORMAL;
                        if (isset($hours[$day][$match])) {
                            $day = BusEntry::TYPE_SATURDAY;
                        }
                        if (isset($hours[$day][$match])) {
                            $day = BusEntry::TYPE_SUNDAY;
                        }
                        if (!count($minutes)) {
                            $hours[$day][$match] = false;
                        } else {
                            $hours[$day][$match] = $minutes;
                        }
                    }
                    //if line already exists
                    if (isset($lines[$line])) {
                        foreach ($lines[$line] as $type => $hrs) {
                            foreach ($hrs as $hr_key => $hr) {
                                if ((!isset($lines[$line][$type][$hr_key]) || !is_array($lines[$line][$type][$hr_key])) && isset($hours[$type][$hr_key])) {
                                    $lines[$line][$type][$hr_key] = $hours[$type][$hr_key];
                                } else if(isset($hours[$type]) && isset($hours[$type][$hr_key]) && is_array($hours[$type][$hr_key])) {
                                    $lines[$line][$type][$hr_key] = array_merge($lines[$line][$type][$hr_key], $hours[$type][$hr_key]);
                                    sort($lines[$line][$type][$hr_key]);
                                }
                            }
                        }
                    } else {

                        $lines[$line] = $hours;
                    }

                });
                foreach ($lines as $line => $values) {
                    if (!isset($entries["lines"][$line])) {
                        $busLine = new BusLine();
                        $busLine->setName($line);
                        $this->em->persist($busLine);
                        $entries["lines"][$line] = $busLine;
                    }
                    if (!isset($entries['busStops'][$busStopName])) {
                        $busStop = new BusStop();
                        $busStop->setName($busStopName);
                        $this->em->persist($busStop);
                        $entries['busStops'][$busStopName] = $busStop;
                    }
                    foreach ($values as $type => $hours) {
                        foreach ($hours as $hr => $minutes) {
                            if (!$minutes) {
                                continue;
                            }
                            foreach ($minutes as $min) {
                                $busEntry = new BusEntry();
                                $busEntry->setBusStop($entries['busStops'][$busStopName]);
                                $busEntry->setBusLine($entries["lines"][$line]);
                                $stopAt = \DateTime::createFromFormat("H:i", $hr.":".preg_replace("/([a-zA-Z]*)/", "", $min));
                                $busEntry->setStopAt($stopAt);
                                $busEntry->setType($type);
                                $this->em->persist($busEntry);
                            }
                        }
                    }
                }
            }


        });
        $this->em->flush();
        $this->em->getConnection()->commit();

    }
}