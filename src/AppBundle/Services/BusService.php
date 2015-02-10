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
     * @return array list of bus stops
     */
    public function getBusStops()
    {
        $client = new Client();

        $crawler = $client->request("GET", "http://rozklady.mpk.krakow.pl/aktualne/przystan.htm");
        $busStops = array();
        $crawler->filter("table a")->each(function ($node) use(&$busStops) {
            if ($node->text()) {
                $busStops[] = $node->text();
            }
        });

        return $busStops;

    }

    /**
     * @param $busStop
     *
     * @return array entries for bus stop
     */
    public function getEntries($busStop)
    {

        $bs = $this->em->getRepository("AppBundle:BusStop")->findOneBy(array("name" => $busStop));
        //if there are cached data, still valid, return data from db
        if ($bs && $bs->getUpdatedAt()->add(\DateInterval::createFromDateString('1 day')) > new \DateTime()) {
            return $this->em->getRepository("AppBundle:BusEntry")->findBusEntries($bs);
        }

        $client = new Client();
        //downloading main page for bus stops
        $crawler = $client->request("GET", "http://rozklady.mpk.krakow.pl/aktualne/przystan.htm");


        $this->em->getConnection()->beginTransaction();
        if ($bs) {
            //deleting old data from database
            $this->em->createQuery("DELETE FROM AppBundle:BusEntry be WHERE be.busStop = :busStop")->execute(array("busStop" => $bs));
            $this->em->getConnection()->prepare("ALTER TABLE bus_entry AUTO_INCREMENT = 1")->execute();
        }
        //iterating bus stops
        $crawler->filter("table a")->each(function ($node) use($bs, $busStop, $client) {
            if ($node->text() == $busStop) {

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
                $busLines = array();
                foreach ($lines as $line => $values) {
                    $lineParts = explode("- >", $line);

                    $lineName = $lineParts[0];
                    if (!isset($busLines[$lineName])) {
                        $busLine = $this->em->getRepository("AppBundle:BusLine")->findOneBy(array("name" => $lineName));
                        if (!$busLine) {
                            $busLine = new BusLine();
                            $busLine->setName($lineName);
                            $this->em->persist($busLine);
                        }
                        $busLines[$lineName] = $busLine;
                    }
                    if (!$bs) {
                        $bs = new BusStop();
                        $bs->setName($busStop);
                        $this->em->persist($bs);
                    }
                    $bs->setUpdatedAt(new \DateTime());
                    foreach ($values as $type => $hours) {
                        foreach ($hours as $hr => $minutes) {
                            if (!$minutes) {
                                continue;
                            }
                            //add data into db
                            foreach ($minutes as $min) {
                                $busEntry = new BusEntry();
                                $busEntry->setBusStop($bs);
                                $busEntry->setBusLine($busLines[$lineName]);
                                $busEntry->setDirection(trim($lineParts[1]));
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
        if (!$bs) {
            $bs = $this->em->getRepository("AppBundle:BusStop")->findOneBy(array("name" => $busStop));
        }
        //return new data from db
        return $this->em->getRepository("AppBundle:BusEntry")->findBusEntries($bs);

    }
}